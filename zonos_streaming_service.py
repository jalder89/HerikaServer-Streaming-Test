#!/usr/bin/env python3
"""
Zonos Streaming Service for HerikaServer Integration

This service provides a REST API for Zonos TTS streaming functionality,
allowing HerikaServer to generate high-quality streaming audio with voice cloning.
"""

import asyncio
import json
import logging
import os
import tempfile
import time
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional, Generator

import torch
import torchaudio
from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import StreamingResponse
from pydantic import BaseModel
import uvicorn

# Import Zonos modules
try:
    from zonos.conditioning import make_cond_dict
    from zonos.model import Zonos
except ImportError as e:
    print(f"Error importing Zonos modules: {e}")
    print("Please ensure Zonos is properly installed and in your Python path")
    exit(1)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="Zonos Streaming Service", version="1.0.0")

# Global model instance
model: Optional[Zonos] = None
speaker_embeddings: Dict[str, torch.Tensor] = {}
model_config = {}

class TTSRequest(BaseModel):
    text: str
    speaker_voice: Optional[str] = None
    language: str = "en-us"
    chunk_schedule: Optional[List[int]] = None
    chunk_overlap: int = 2
    cfg_scale: float = 2.0
    max_new_tokens: int = 2580
    streaming: bool = True
    character_name: Optional[str] = None

class TTSBatchRequest(BaseModel):
    sentences: List[str]
    speaker_voice: Optional[str] = None
    language: str = "en-us"
    chunk_schedule: Optional[List[int]] = None
    chunk_overlap: int = 2
    cfg_scale: float = 2.0
    max_new_tokens: int = 2580
    streaming: bool = True
    character_name: Optional[str] = None

@app.on_event("startup")
async def startup_event():
    """Initialize Zonos model on startup"""
    global model, model_config
    
    logger.info("Starting Zonos Streaming Service...")
    
    # Default configuration - force transformer for RTX 50xx compatibility
    model_config = {
        "model_type": "transformer",
        "device": "cuda" if torch.cuda.is_available() else "cpu",
        "repo_id": "Zyphra/Zonos-v0.1-transformer"
    }
    
    # Note: RTX 50xx series only supports transformer model
    # Other GPUs can use either transformer or hybrid
    
    try:
        logger.info(f"Loading Zonos model on {model_config['device']}...")
        model = Zonos.from_pretrained(
            model_config["repo_id"], 
            device=model_config["device"]
        )
        model.requires_grad_(False).eval()
        logger.info("Zonos model loaded successfully!")
        
    except Exception as e:
        logger.error(f"Failed to load Zonos model: {e}")
        raise e

def get_speaker_embedding(voice_file: str, character_name: Optional[str] = None) -> torch.Tensor:
    """Get or create speaker embedding from voice file"""
    global speaker_embeddings
    
    # Use character name as cache key if provided, otherwise use file path
    cache_key = character_name if character_name else voice_file
    
    if cache_key in speaker_embeddings:
        logger.info(f"Using cached speaker embedding for: {cache_key}")
        return speaker_embeddings[cache_key]
    
    try:
        # Load audio file
        if not os.path.exists(voice_file):
            # Try relative to service directory
            service_dir = Path(__file__).parent
            voice_file = service_dir / voice_file
        
        if not os.path.exists(voice_file):
            raise FileNotFoundError(f"Voice file not found: {voice_file}")
        
        logger.info(f"Loading voice file: {voice_file}")
        wav, sr = torchaudio.load(str(voice_file))
        
        # Generate speaker embedding
        speaker = model.make_speaker_embedding(wav, sr)
        
        # Cache the embedding
        speaker_embeddings[cache_key] = speaker
        logger.info(f"Created and cached speaker embedding for: {cache_key}")
        
        return speaker
        
    except Exception as e:
        logger.error(f"Failed to create speaker embedding: {e}")
        raise HTTPException(status_code=400, detail=f"Failed to process voice file: {e}")

def create_sentence_generator(sentences: List[str], speaker: torch.Tensor, language: str) -> Generator[Dict, None, None]:
    """Create a generator of conditioning dictionaries for streaming"""
    for sentence in sentences:
        if sentence.strip():  # Skip empty sentences
            yield {
                "text": sentence.strip(),
                "speaker": speaker,
                "language": language,
            }

@app.post("/tts/single")
async def generate_single_tts(request: TTSRequest):
    """Generate TTS for a single text input"""
    global model
    
    if model is None:
        raise HTTPException(status_code=503, detail="Model not loaded")
    
    try:
        # Get speaker embedding
        if request.speaker_voice:
            speaker = get_speaker_embedding(request.speaker_voice, request.character_name)
        else:
            # Use default speaker embedding
            speaker = torch.zeros(1, 256, device=model.device)  # Default embedding
        
        # Create conditioning
        cond_dict = make_cond_dict(
            text=request.text,
            speaker=speaker,
            language=request.language
        )
        conditioning = model.prepare_conditioning(cond_dict)
        
        if request.streaming:
            # Streaming generation
            def generate_stream():
                chunk_schedule = request.chunk_schedule or [16, 9, 12, 15, 20, 30, 50, 80]
                
                stream_generator = model.stream(
                    prefix_conditioning=conditioning,
                    chunk_schedule=chunk_schedule,
                    chunk_overlap=request.chunk_overlap,
                    cfg_scale=request.cfg_scale,
                    max_new_tokens=request.max_new_tokens
                )
                
                for audio_chunk in stream_generator:
                    if isinstance(audio_chunk, torch.Tensor):
                        # Convert to bytes
                        audio_bytes = audio_chunk.cpu().numpy().tobytes()
                        yield audio_bytes
            
            return StreamingResponse(
                generate_stream(),
                media_type="application/octet-stream",
                headers={
                    "X-Sample-Rate": str(model.autoencoder.sampling_rate),
                    "X-Channels": "1",
                    "X-Dtype": "float32"
                }
            )
        else:
            # Non-streaming generation
            codes = model.generate(
                conditioning,
                max_new_tokens=request.max_new_tokens,
                cfg_scale=request.cfg_scale
            )
            audio = model.autoencoder.decode(codes)[0]
            
            # Save to temporary file
            with tempfile.NamedTemporaryFile(suffix=".wav", delete=False) as tmp_file:
                torchaudio.save(tmp_file.name, audio.cpu(), model.autoencoder.sampling_rate)
                return {"audio_file": tmp_file.name, "sample_rate": model.autoencoder.sampling_rate}
                
    except Exception as e:
        logger.error(f"TTS generation failed: {e}")
        raise HTTPException(status_code=500, detail=f"TTS generation failed: {e}")

@app.post("/tts/batch")
async def generate_batch_tts(request: TTSBatchRequest):
    """Generate streaming TTS for multiple sentences (conversation-style)"""
    global model
    
    if model is None:
        raise HTTPException(status_code=503, detail="Model not loaded")
    
    try:
        # Get speaker embedding
        if request.speaker_voice:
            speaker = get_speaker_embedding(request.speaker_voice, request.character_name)
        else:
            # Use default speaker embedding
            speaker = torch.zeros(1, 256, device=model.device)
        
        if request.streaming:
            # Streaming batch generation
            def generate_batch_stream():
                chunk_schedule = request.chunk_schedule or [16, 9, 12, 15, 20, 30, 50, 80]
                
                # Create generator for sentences
                sentence_generator = create_sentence_generator(
                    request.sentences, speaker, request.language
                )
                
                stream_generator = model.stream(
                    cond_dicts_generator=sentence_generator,
                    chunk_schedule=chunk_schedule,
                    chunk_overlap=request.chunk_overlap,
                    cfg_scale=request.cfg_scale,
                    max_new_tokens=request.max_new_tokens,
                    mark_boundaries=True
                )
                
                for item in stream_generator:
                    if isinstance(item, torch.Tensor):
                        # Audio chunk
                        audio_bytes = item.cpu().numpy().tobytes()
                        yield b"AUDIO:" + len(audio_bytes).to_bytes(4, 'little') + audio_bytes
                    elif isinstance(item, str):
                        # Sentence boundary marker
                        sentence_bytes = item.encode('utf-8')
                        yield b"SENTENCE:" + len(sentence_bytes).to_bytes(4, 'little') + sentence_bytes
            
            return StreamingResponse(
                generate_batch_stream(),
                media_type="application/octet-stream",
                headers={
                    "X-Sample-Rate": str(model.autoencoder.sampling_rate),
                    "X-Channels": "1",
                    "X-Dtype": "float32",
                    "X-Stream-Type": "mixed"
                }
            )
        else:
            # Non-streaming batch - generate all at once
            all_text = " ".join(request.sentences)
            
            cond_dict = make_cond_dict(
                text=all_text,
                speaker=speaker,
                language=request.language
            )
            conditioning = model.prepare_conditioning(cond_dict)
            
            codes = model.generate(
                conditioning,
                max_new_tokens=request.max_new_tokens,
                cfg_scale=request.cfg_scale
            )
            audio = model.autoencoder.decode(codes)[0]
            
            # Save to temporary file
            with tempfile.NamedTemporaryFile(suffix=".wav", delete=False) as tmp_file:
                torchaudio.save(tmp_file.name, audio.cpu(), model.autoencoder.sampling_rate)
                return {"audio_file": tmp_file.name, "sample_rate": model.autoencoder.sampling_rate}
                
    except Exception as e:
        logger.error(f"Batch TTS generation failed: {e}")
        raise HTTPException(status_code=500, detail=f"Batch TTS generation failed: {e}")

@app.get("/status")
async def get_status():
    """Get service status"""
    global model, model_config
    
    return {
        "status": "running" if model is not None else "loading",
        "model_loaded": model is not None,
        "device": model_config.get("device", "unknown"),
        "model_type": model_config.get("model_type", "unknown"),
        "cached_speakers": len(speaker_embeddings),
        "timestamp": datetime.now().isoformat()
    }

@app.post("/speaker/preload")
async def preload_speaker(voice_file: str, character_name: Optional[str] = None):
    """Preload a speaker embedding"""
    try:
        speaker = get_speaker_embedding(voice_file, character_name)
        cache_key = character_name if character_name else voice_file
        return {
            "status": "success", 
            "message": f"Speaker embedding loaded for: {cache_key}",
            "embedding_shape": list(speaker.shape)
        }
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))

@app.delete("/speaker/{character_name}")
async def clear_speaker_cache(character_name: str):
    """Clear a specific speaker from cache"""
    global speaker_embeddings
    
    if character_name in speaker_embeddings:
        del speaker_embeddings[character_name]
        return {"status": "success", "message": f"Cleared speaker cache for: {character_name}"}
    else:
        return {"status": "not_found", "message": f"Speaker not found in cache: {character_name}"}

@app.delete("/speaker")
async def clear_all_speakers():
    """Clear all speaker embeddings from cache"""
    global speaker_embeddings
    
    count = len(speaker_embeddings)
    speaker_embeddings.clear()
    return {"status": "success", "message": f"Cleared {count} speaker embeddings from cache"}

if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description="Zonos Streaming Service")
    parser.add_argument("--host", default="127.0.0.1", help="Host to bind to")
    parser.add_argument("--port", type=int, default=8765, help="Port to bind to")
    parser.add_argument("--model", default="transformer", choices=["transformer", "hybrid"], help="Model type")
    parser.add_argument("--device", default="auto", help="Device (cuda/cpu/auto)")
    
    args = parser.parse_args()
    
    # Update model config
    model_config["model_type"] = args.model
    if args.device == "auto":
        model_config["device"] = "cuda" if torch.cuda.is_available() else "cpu"
    else:
        model_config["device"] = args.device
    
    # Set appropriate model repo based on selection
    if args.model == "hybrid":
        model_config["repo_id"] = "Zyphra/Zonos-v0.1-hybrid"
        logger.info("Using hybrid model (higher quality, requires compatible GPU)")
    else:
        model_config["repo_id"] = "Zyphra/Zonos-v0.1-transformer"
        logger.info("Using transformer model (faster, RTX 50xx compatible)")
    
    # Run the service
    uvicorn.run(app, host=args.host, port=args.port)
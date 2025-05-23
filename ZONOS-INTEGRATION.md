# Zonos Streaming TTS Integration

This document describes the new Zonos streaming TTS integration for HerikaServer, which provides high-quality voice cloning and streaming audio generation for enhanced Skyrim character interactions.

## Features

- **Real-time Streaming**: Progressive audio generation with minimal latency
- **Voice Cloning**: Advanced speaker embedding for consistent character voices  
- **Seamless Integration**: Drop-in replacement for existing TTS providers
- **Configurable Performance**: Adaptive chunk scheduling for optimal quality/speed balance
- **Crossfading**: Smooth audio transitions between chunks eliminate gaps

## Prerequisites

### Python Dependencies
The Zonos streaming service requires Python 3.8+ with the following packages:

```bash
# Install Zonos and dependencies
pip install torch torchaudio transformers
pip install fastapi uvicorn
pip install pydantic

# For Zonos itself, follow the installation from the Zonos-Streaming-Test directory:
cd Zonos-Streaming-Test
pip install -e .
```

### System Requirements
- **GPU Recommended**: CUDA-compatible GPU for optimal performance
- **CPU Fallback**: CPU inference supported but slower
- **Memory**: 4GB+ GPU memory or 8GB+ system RAM
- **Storage**: 2GB for model files

## Installation

### 1. Copy Configuration
Ensure your `conf/conf.php` includes the Zonos settings (already added to `conf.sample.php`):

```php
$TTS["ZONOS"]["endpoint"]='http://127.0.0.1:8765';
$TTS["ZONOS"]["model"]='transformer';  // 'transformer' or 'hybrid'
$TTS["ZONOS"]["device"]='cuda';        // 'cuda' or 'cpu'
$TTS["ZONOS"]["language"]='en-us';
$TTS["ZONOS"]["speaker_voice"]='assets/exampleaudio.mp3';
$TTS["ZONOS"]["chunk_schedule"]='[16,9,12,15,20,30,50,80]';
$TTS["ZONOS"]["chunk_overlap"]=2;
$TTS["ZONOS"]["cfg_scale"]=2.0;
$TTS["ZONOS"]["max_new_tokens"]=2580;
$TTS["ZONOS"]["streaming_enabled"]=true;
$TTS["ZONOS"]["timeout"]=30;

$TTSFUNCTION="zonos";  // Enable Zonos as TTS provider
```

### 2. Start Zonos Streaming Service

```bash
# From HerikaServer directory
python zonos_streaming_service.py --host 127.0.0.1 --port 8765 --model transformer --device cuda
```

**Service Options:**
- `--host`: IP to bind to (default: 127.0.0.1)
- `--port`: Port to bind to (default: 8765)
- `--model`: Model type - `transformer` (faster) or `hybrid` (higher quality)
- `--device`: `cuda`, `cpu`, or `auto`

### 3. Configure Voice Files
Place reference voice audio files for your characters:

```bash
# Example voice file structure
HerikaServer-Streaming-Test/
├── data/voices/
│   ├── herika.mp3      # Default Herika voice
│   ├── lydia.wav       # Lydia companion voice
│   └── general.mp3     # Fallback voice
```

Update configuration to point to your voice file:
```php
$TTS["ZONOS"]["speaker_voice"]='data/voices/herika.mp3';
```

## Configuration Reference

### Basic Settings
| Setting | Description | Default |
|---------|-------------|---------|
| `endpoint` | Zonos service URL | `http://127.0.0.1:8765` |
| `model` | Model type: `transformer` (RTX 50xx) or `hybrid` (other GPUs) | `transformer` |
| `device` | Computing device: `cuda`, `cpu` | `cuda` |
| `language` | Language code (en-us, es-es, etc.) | `en-us` |
| `speaker_voice` | Path to reference voice audio | `assets/exampleaudio.mp3` |

### Streaming Settings
| Setting | Description | Default |
|---------|-------------|---------|
| `streaming_enabled` | Enable progressive streaming | `true` |
| `chunk_schedule` | JSON array of chunk sizes | `[16,9,12,15,20,30,50,80]` |
| `chunk_overlap` | Overlap tokens for crossfading | `2` |
| `timeout` | HTTP request timeout (seconds) | `30` |

### Quality Settings
| Setting | Description | Default |
|---------|-------------|---------|
| `cfg_scale` | Classifier-free guidance scale | `2.0` |
| `max_new_tokens` | Maximum audio length (tokens) | `2580` |

## Usage

### Basic Usage
Once configured, Zonos will automatically handle TTS requests when `$TTSFUNCTION="zonos"` is set. No code changes needed in existing Skyrim mod integration.

### Voice Cloning Setup
1. **Record Reference Audio**: 10-30 seconds of clear speech
2. **Prepare Audio File**: Save as MP3/WAV, place in accessible location
3. **Update Configuration**: Point `speaker_voice` to your audio file
4. **Character-Specific Voices**: Use different voice files for different NPCs

### Performance Tuning

#### For Low Latency (Fast Response)
```php
$TTS["ZONOS"]["chunk_schedule"]='[8,9,10,12,15]';  // Smaller chunks
$TTS["ZONOS"]["chunk_overlap"]=1;                  // Less overlap
$TTS["ZONOS"]["model"]='transformer';               // Faster model
```

#### For High Quality (Best Audio)
```php
$TTS["ZONOS"]["chunk_schedule"]='[25,30,40,50,80]'; // Larger chunks
$TTS["ZONOS"]["chunk_overlap"]=3;                   // More overlap
$TTS["ZONOS"]["model"]='hybrid';                    // Higher quality model (not for RTX 50xx)
$TTS["ZONOS"]["cfg_scale"]=3.0;                     // Higher guidance
```

**Note**: RTX 50xx series GPUs should use `transformer` model for compatibility.

## API Endpoints

The Zonos streaming service provides REST endpoints:

### Service Status
```
GET /status
```
Returns service health and model information.

### Single TTS
```
POST /tts/single
Content-Type: application/json

{
    "text": "Hello, this is a test",
    "speaker_voice": "path/to/voice.mp3",
    "language": "en-us",
    "streaming": true
}
```

### Batch TTS
```
POST /tts/batch
Content-Type: application/json

{
    "sentences": ["Hello there!", "How are you?"],
    "speaker_voice": "path/to/voice.mp3",
    "character_name": "Herika",
    "streaming": true
}
```

### Speaker Management
```
POST /speaker/preload          # Preload speaker embedding
DELETE /speaker/{name}         # Clear specific speaker cache
DELETE /speaker               # Clear all speaker cache
```

## Troubleshooting

### Service Won't Start
- **Check Python Dependencies**: Ensure all packages installed
- **GPU Issues**: Try `--device cpu` if CUDA problems
- **Port Conflicts**: Change `--port` if 8765 is in use
- **Model Download**: First run downloads models (may take time)

### Audio Quality Issues
- **Crackling/Artifacts**: Increase `chunk_overlap` 
- **Robotic Voice**: Check voice file quality, try different speaker
- **Cut-off Audio**: Increase `max_new_tokens`
- **Slow Generation**: Use `transformer` model, smaller chunks

### Integration Issues
- **No Audio Output**: Check service status at `/status` endpoint
- **Connection Errors**: Verify `endpoint` URL in configuration
- **Cache Problems**: Clear `/soundcache/` directory
- **Timeout Errors**: Increase `timeout` setting

### Performance Optimization
- **First Request Slow**: Models load on first use (normal)
- **Memory Usage**: Restart service periodically for long sessions
- **CPU Usage**: Use GPU device for better performance

## Advanced Features

### Character-Specific Voices
Configure different voices per character by modifying the speaker_voice path based on context:

```php
// In your configuration
if ($GLOBALS["HERIKA_NAME"] == "Lydia") {
    $TTS["ZONOS"]["speaker_voice"] = "data/voices/lydia.wav";
} elseif ($GLOBALS["HERIKA_NAME"] == "Serana") {
    $TTS["ZONOS"]["speaker_voice"] = "data/voices/serana.mp3";
}
```

### Conversation Context
The batch TTS endpoint supports conversation-style generation with speaker consistency across multiple sentences.

### Production Deployment
For production use:
- Use process manager (systemd, supervisord) for service reliability
- Configure reverse proxy (nginx) for load balancing
- Monitor GPU memory usage and restart service as needed
- Consider running multiple service instances for load distribution

## Files Modified

The integration adds/modifies these files:
- `conf/conf.sample.php` - Added Zonos configuration section
- `tts/tts-zonos.php` - New Zonos TTS provider
- `lib/chat_helper_functions.php` - Added Zonos to TTS selection logic
- `zonos_streaming_service.py` - New Python streaming service

## Compatibility

- **Backward Compatible**: Existing TTS providers continue to work
- **Skyrim Mod**: No changes needed to SKSE plugin
- **Configuration**: New settings are optional with sensible defaults
- **Performance**: Streaming provides benefits without breaking existing functionality

## Support

For issues specific to Zonos integration:
1. Check service logs for detailed error messages
2. Verify model loading and GPU availability
3. Test with simple text before complex conversations
4. Monitor system resources during operation

For general HerikaServer issues, refer to the main documentation.
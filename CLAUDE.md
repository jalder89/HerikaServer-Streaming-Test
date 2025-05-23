# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HerikaServer is a PHP-based CHIM Server that bridges the Skyrim SKSE plugin with AI providers, enabling meaningful AI NPC interactions with long-term memory, function calling, and multi-modal AI services.

## Architecture

### Core Entry Points
- **main.php**: Core engine entry point for processing AI requests
- **ui/index.php**: Web dashboard for configuration and monitoring  
- **stream.php/streamv2.php**: API endpoints for Skyrim mod communication

### Configuration System
- **Primary config**: `conf/conf.php` (copy from `conf.sample.php` to start)
- **Schema validation**: `conf/conf_schema.json` defines all configuration parameters
- **UI-based editor**: `ui/conf_editor.php` for web-based configuration
- **Configuration wizard**: `ui/conf_wizard.php` for initial setup

### Modular Connector Architecture
Located in `/connector/`, these adapters handle different AI services:
- **openai.php**: OpenAI/ChatGPT integration with function calling
- **anthropic.php**: Anthropic/Claude integration
- **koboldcpp.php, llamacpp.php**: Local LLM integrations
- **template.php**: Base template for creating new connectors

Each connector implements a standard interface for AI communication and supports templating systems in `/connector/templates/`.

### Memory System
- **memory_helper_vectordb.php**: ChromaDB integration for long-term character memory
- **memory_helper_embeddings.php**: Text embedding utilities
- Vector database stores character interactions, events, and context for retrieval

### Function Calling System
- **functions/functions.php**: Game action functions (MoveTo, Attack, Follow, etc.)
- Enables NPCs to perform in-game actions through AI commands
- Functions are exposed to AI connectors that support function calling

### Multi-Modal Pipeline
- **TTS Services** (`/tts/`): Azure, ElevenLabs, OpenAI, XTTS, Mimic3, Coqui-AI, **Zonos (NEW)**
  - **Zonos**: Advanced streaming TTS with voice cloning and real-time generation
  - Requires Python service: `zonos_streaming_service.py`
  - See `ZONOS-INTEGRATION.md` for complete setup guide
- **STT Services** (`/stt/`): Whisper, Azure, Deepgram, Local Whisper
- **ITT Services** (`/itt/`): Image-to-text for visual context

## Development Commands

### Initial Setup
```bash
# Copy configuration template
cp conf/conf.sample.php conf/conf.php
# Edit conf.php with API keys and service endpoints

# Install PHP dependencies for TTS services
cd tts && composer install && cd ..

# Start Zonos streaming service (if using Zonos TTS)
./start_zonos_service.sh
# Or manually: python3 zonos_streaming_service.py --host 127.0.0.1 --port 8765
```

### Debug and Testing
```bash
# Run comprehensive test suite
cd debug && ./debug_tests.sh

# Run memory and conversation tests  
cd debug && ./misc_tests.sh

# Test specific components
php debug/simple_llm_request.php
php debug/simple_tts_test.php
php debug/simple_stt_test.php
```

### UI and Configuration
```bash
# Access web dashboard
# Navigate to ui/index.php in browser

# Test system health
# Navigate to ui/tests.php in browser

# Component-specific tests
php ui/tests/tts-test-azure.php
php ui/tests/vector-test-chromadb.php
```

### Database Management
```bash
# Initialize database
php ui/cmd/install-db.php

# Export/Import database
# Use ui/export_db.php and ui/import_db.php via web interface
```

## Key Integration Points

1. **Skyrim Mod Communication**: Receives requests via `stream.php` endpoints
2. **AI Service Routing**: Routes to appropriate connector based on configuration
3. **Memory Augmentation**: Retrieves relevant context from vector database
4. **Function Processing**: Executes game actions when AI requests them
5. **Multi-Modal Output**: Generates TTS audio and returns commands to Skyrim

## Important Notes

- All AI service API keys are configured in `conf/conf.php`
- Memory system requires ChromaDB setup for vector storage
- Function calling enables NPCs to perform game actions (MoveTo, Attack, etc.)
- UI dashboard provides real-time monitoring at `ui/index.php`
- Debug scripts in `/debug/` folder test individual components
- Configuration wizard at `ui/conf_wizard.php` guides initial setup
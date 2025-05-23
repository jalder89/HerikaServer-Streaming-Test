#!/bin/bash

# Zonos Streaming Service Startup Script
# This script starts the Zonos TTS streaming service for HerikaServer

echo "=== Zonos Streaming Service for HerikaServer ==="
echo ""

# Configuration
HOST=${ZONOS_HOST:-"127.0.0.1"}
PORT=${ZONOS_PORT:-"8765"}
MODEL=${ZONOS_MODEL:-"transformer"}
DEVICE=${ZONOS_DEVICE:-"auto"}

# Check if Python is available
if ! command -v python3 &> /dev/null; then
    echo "Error: Python 3 is required but not found in PATH"
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "zonos_streaming_service.py" ]; then
    echo "Error: zonos_streaming_service.py not found in current directory"
    echo "Please run this script from the HerikaServer-Streaming-Test directory"
    exit 1
fi

# Check for Zonos installation
python3 -c "import zonos" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "Warning: Zonos module not found. Please ensure Zonos is installed:"
    echo "  cd ../Zonos-Streaming-Test"
    echo "  pip install -e ."
    echo ""
fi

echo "Starting Zonos Streaming Service..."
echo "  Host: $HOST"
echo "  Port: $PORT" 
echo "  Model: $MODEL"
echo "  Device: $DEVICE"
echo ""
echo "Service will be available at: http://$HOST:$PORT"
echo "Press Ctrl+C to stop the service"
echo ""
echo "Configure HerikaServer with:"
echo "  \$TTSFUNCTION=\"zonos\";"
echo "  \$TTS[\"ZONOS\"][\"endpoint\"]=\"http://$HOST:$PORT\";"
echo ""

# Start the service
python3 zonos_streaming_service.py \
    --host "$HOST" \
    --port "$PORT" \
    --model "$MODEL" \
    --device "$DEVICE"
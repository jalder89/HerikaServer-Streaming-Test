#!/bin/bash

echo "Applying Zonos Streaming Updates..."

# Check if Zonos is installed
if [ ! -d "/home/dwemer/Zonos" ]; then
    echo "Error: Zonos not found. Please install Zonos first."
    exit 1
fi

cd /home/dwemer/Zonos
source ./bin/activate

# Backup original files
echo "Backing up original files..."
mkdir -p backup_original
cp zonos/model.py backup_original/ 2>/dev/null || true
cp zonos/autoencoder.py backup_original/ 2>/dev/null || true

# Copy streaming update files
echo "Installing streaming updates..."
cp /var/www/html/HerikaServer/zonos/Streaming-Update/model.py zonos/
cp /var/www/html/HerikaServer/zonos/Streaming-Update/autoencoder.py zonos/
cp /var/www/html/HerikaServer/zonos/Streaming-Update/speaker_cloning.py zonos/
cp /var/www/html/HerikaServer/zonos/Streaming-Update/streaming.py zonos/

# Reinstall in development mode to ensure changes take effect
echo "Reinstalling Zonos with streaming support..."
pip install -e .

# Create marker file to indicate streaming is installed
echo "$(date): Zonos streaming updates applied" > .streaming_enabled

echo "Zonos streaming updates applied successfully!"
echo "The 'stream' method should now be available on Zonos models."

deactivate
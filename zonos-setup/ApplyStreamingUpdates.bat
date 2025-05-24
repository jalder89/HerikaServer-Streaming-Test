@echo off
echo Applying Zonos Streaming Updates...
echo.

echo Copying update script to WSL...
copy /y apply_streaming_updates.sh \\wsl.localhost\DwemerAI4Skyrim3\home\dwemer\
if errorlevel 1 (
    echo Error: Could not copy update script to WSL
    pause
    exit /b 1
)

echo Setting permissions and running update...
wsl -d DwemerAI4Skyrim3 -- sed -i 's/\r$//' /home/dwemer/apply_streaming_updates.sh
wsl -d DwemerAI4Skyrim3 -- chown dwemer:dwemer /home/dwemer/apply_streaming_updates.sh
wsl -d DwemerAI4Skyrim3 -- chmod +x /home/dwemer/apply_streaming_updates.sh
wsl -d DwemerAI4Skyrim3 -u dwemer -- /home/dwemer/apply_streaming_updates.sh

echo.
echo Streaming updates applied. You can now use Zonos streaming functionality.
echo Press any key to exit.
pause
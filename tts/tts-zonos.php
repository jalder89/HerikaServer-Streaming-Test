<?php

/**
 * Zonos TTS Provider for HerikaServer
 * 
 * Provides high-quality streaming TTS with voice cloning capabilities
 * using the Zonos neural audio synthesis model.
 */


function tts($textString, $mood, $stringforhash) {
    
    // Cache check
    if (!isset($GLOBALS["AVOID_TTS_CACHE"])) {
        $cachePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "soundcache/" . md5(trim($stringforhash)) . ".wav";
        if (file_exists($cachePath)) {
            return $cachePath;
        }
    }
    
    $startTime = microtime(true);
    
    // Configuration
    $endpoint = $GLOBALS["TTS"]["ZONOS"]["endpoint"];
    $model = $GLOBALS["TTS"]["ZONOS"]["model"];
    $device = $GLOBALS["TTS"]["ZONOS"]["device"];
    $language = $GLOBALS["TTS"]["ZONOS"]["language"];
    $chunkSchedule = json_decode($GLOBALS["TTS"]["ZONOS"]["chunk_schedule"], true);
    $chunkOverlap = intval($GLOBALS["TTS"]["ZONOS"]["chunk_overlap"]);
    $cfgScale = floatval($GLOBALS["TTS"]["ZONOS"]["cfg_scale"]);
    $maxNewTokens = intval($GLOBALS["TTS"]["ZONOS"]["max_new_tokens"]);
    $streamingEnabled = (bool)$GLOBALS["TTS"]["ZONOS"]["streaming_enabled"];
    $timeout = intval($GLOBALS["TTS"]["ZONOS"]["timeout"]);
    
    // Character name for speaker caching (use Herika name if available)
    $characterName = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : null;
    
    // Voice selection - exact same pattern as XTTS
    $voice = isset($GLOBALS["TTS"]["FORCED_VOICE_DEV"]) ? $GLOBALS["TTS"]["FORCED_VOICE_DEV"] : $GLOBALS["TTS"]["ZONOS"]["voiceid"];
    if (empty($voice)) {
        $voice = $GLOBALS["TTS"]["ZONOS"]["voiceid"];
    }
    
    // Load voice data from JSON file if it exists (same as XTTS)
    $voiceJsonPath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "{$voice}.json";
    if (file_exists($voiceJsonPath)) {
        $data_voice = json_decode(file_get_contents($voiceJsonPath), true);
        $speakerVoice = isset($data_voice['speaker_voice']) ? $data_voice['speaker_voice'] : $GLOBALS["TTS"]["ZONOS"]["speaker_voice"];
        $GLOBALS["DEBUG_DATA"][] = "Loaded voice from JSON: $voice -> $speakerVoice";
    } else {
        $speakerVoice = $GLOBALS["TTS"]["ZONOS"]["speaker_voice"];
        $GLOBALS["DEBUG_DATA"][] = "No JSON for voice '$voice', using default: $speakerVoice";
    }
    
    // Check if speaker voice file exists
    $speakerVoiceFullPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $speakerVoice;
    if (!file_exists($speakerVoiceFullPath)) {
        $GLOBALS["DEBUG_DATA"][] = "WARNING: Speaker voice file not found: $speakerVoiceFullPath";
    } else {
        $GLOBALS["DEBUG_DATA"][] = "Speaker voice file exists: $speakerVoiceFullPath";
    }
    
    try {
        
        // Check if Zonos service is available
        if (!isZonosServiceAvailable($endpoint)) {
            $GLOBALS["DEBUG_DATA"][] = "Zonos service not available at $endpoint";
            return false;
        }
        
        // Convert relative path to absolute path for Zonos service
        $absoluteSpeakerVoice = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $speakerVoice;
        $absoluteSpeakerVoice = realpath($absoluteSpeakerVoice); // Normalize the path
        
        // Prepare request data
        $requestData = [
            'text' => $textString,
            'speaker_voice' => $absoluteSpeakerVoice,
            'language' => $language,
            'chunk_schedule' => $chunkSchedule,
            'chunk_overlap' => $chunkOverlap,
            'cfg_scale' => $cfgScale,
            'max_new_tokens' => $maxNewTokens,
            'streaming' => $streamingEnabled,
            'character_name' => $characterName
        ];
        
        // Debug logging
        $GLOBALS["DEBUG_DATA"][] = "Zonos request data: " . json_encode($requestData);
        
        // Generate audio
        if ($streamingEnabled) {
            $audioData = generateStreamingTTS($endpoint, $requestData, $timeout);
        } else {
            $audioData = generateSingleTTS($endpoint, $requestData, $timeout);
        }
        
        if ($audioData === false) {
            $GLOBALS["DEBUG_DATA"][] = "Failed to generate TTS with Zonos";
            return false;
        }
        
        // Save audio to cache
        $cachePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "soundcache/" . md5(trim($stringforhash)) . ".wav";
        
        if (file_put_contents($cachePath, $audioData) === false) {
            $GLOBALS["DEBUG_DATA"][] = "Failed to save Zonos audio to cache";
            return false;
        }
        
        // Log timing
        $totalTime = microtime(true) - $startTime;
        $debugPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "soundcache/" . md5(trim($stringforhash)) . ".txt";
        file_put_contents($debugPath, trim($textString) . "\n\rtotal call time: " . $totalTime . " seconds\n\rZonos TTS with streaming: " . ($streamingEnabled ? "enabled" : "disabled"));
        
        $GLOBALS["DEBUG_DATA"][] = $totalTime . " seconds in Zonos TTS call";
        
        return "soundcache/" . md5(trim($stringforhash)) . ".wav";
        
    } catch (Exception $e) {
        $GLOBALS["DEBUG_DATA"][] = "Zonos TTS error: " . $e->getMessage();
        
        // Save error log
        $errorPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "soundcache/" . md5(trim($stringforhash)) . ".err";
        file_put_contents($errorPath, "Zonos TTS Error: " . $e->getMessage() . "\nText: " . $textString);
        
        return false;
    }
}

/**
 * Check if Zonos streaming service is available
 */
function isZonosServiceAvailable($endpoint) {
    $statusUrl = rtrim($endpoint, '/') . '/status';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($statusUrl, false, $context);
    
    if ($response === false) {
        return false;
    }
    
    $status = json_decode($response, true);
    return isset($status['status']) && $status['status'] === 'running';
}

/**
 * Generate TTS using streaming endpoint and collect all chunks
 */
function generateStreamingTTS($endpoint, $requestData, $timeout) {
    $url = rtrim($endpoint, '/') . '/tts/single';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($requestData),
            'timeout' => $timeout
        ]
    ]);
    
    // Open streaming connection
    $handle = fopen($url, 'r', false, $context);
    
    if (!$handle) {
        return false;
    }
    
    // Read response headers to get audio format info
    $metadata = stream_get_meta_data($handle);
    $sampleRate = 44100; // Default
    $channels = 1;
    $dtype = 'float32';
    
    if (isset($metadata['wrapper_data'])) {
        foreach ($metadata['wrapper_data'] as $header) {
            if (strpos($header, 'X-Sample-Rate:') === 0) {
                $sampleRate = intval(trim(substr($header, 14)));
            }
            if (strpos($header, 'X-Channels:') === 0) {
                $channels = intval(trim(substr($header, 11)));
            }
            if (strpos($header, 'X-Dtype:') === 0) {
                $dtype = trim(substr($header, 8));
            }
        }
    }
    
    // Collect all audio chunks
    $audioChunks = [];
    while (!feof($handle)) {
        $chunk = fread($handle, 8192);
        if ($chunk !== false && strlen($chunk) > 0) {
            $audioChunks[] = $chunk;
        }
    }
    
    fclose($handle);
    
    if (empty($audioChunks)) {
        return false;
    }
    
    // Combine all chunks
    $rawAudioData = implode('', $audioChunks);
    
    // Convert raw float32 data to WAV format
    return convertRawToWav($rawAudioData, $sampleRate, $channels);
}

/**
 * Generate TTS using non-streaming endpoint
 */
function generateSingleTTS($endpoint, $requestData, $timeout) {
    $requestData['streaming'] = false;
    $url = rtrim($endpoint, '/') . '/tts/single';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($requestData),
            'timeout' => $timeout
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return false;
    }
    
    $result = json_decode($response, true);
    
    if (!isset($result['audio_file'])) {
        return false;
    }
    
    // Read the temporary audio file from the service
    $tempAudioPath = $result['audio_file'];
    $audioData = file_get_contents($tempAudioPath);
    
    // Clean up temp file on service side (optional, service should handle this)
    // We could make a cleanup request here
    
    return $audioData;
}

/**
 * Convert raw float32 audio data to WAV format
 */
function convertRawToWav($rawData, $sampleRate = 44100, $channels = 1) {
    
    $dataLength = strlen($rawData);
    $numSamples = $dataLength / 4; // 4 bytes per float32 sample
    
    // WAV header
    $header = '';
    $header .= 'RIFF';                          // ChunkID
    $header .= pack('V', 36 + $dataLength);    // ChunkSize
    $header .= 'WAVE';                          // Format
    $header .= 'fmt ';                          // Subchunk1ID
    $header .= pack('V', 16);                   // Subchunk1Size (PCM = 16)
    $header .= pack('v', 3);                    // AudioFormat (3 = IEEE float)
    $header .= pack('v', $channels);            // NumChannels
    $header .= pack('V', $sampleRate);          // SampleRate
    $header .= pack('V', $sampleRate * $channels * 4); // ByteRate
    $header .= pack('v', $channels * 4);        // BlockAlign
    $header .= pack('v', 32);                   // BitsPerSample (32-bit float)
    $header .= 'data';                          // Subchunk2ID
    $header .= pack('V', $dataLength);          // Subchunk2Size
    
    return $header . $rawData;
}

/**
 * Multi-sentence TTS function for batch processing
 * This is an advanced function that can handle conversation-style streaming
 */
function ttsZonosBatch($sentences, $mood, $stringforhash) {
    
    // Cache check
    if (!isset($GLOBALS["AVOID_TTS_CACHE"])) {
        $cachePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "soundcache/" . md5(trim($stringforhash)) . ".wav";
        if (file_exists($cachePath)) {
            return $cachePath;
        }
    }
    
    $startTime = microtime(true);
    
    // Configuration
    $endpoint = $GLOBALS["TTS"]["ZONOS"]["endpoint"];
    $language = $GLOBALS["TTS"]["ZONOS"]["language"];
    $chunkSchedule = json_decode($GLOBALS["TTS"]["ZONOS"]["chunk_schedule"], true);
    $chunkOverlap = intval($GLOBALS["TTS"]["ZONOS"]["chunk_overlap"]);
    $cfgScale = floatval($GLOBALS["TTS"]["ZONOS"]["cfg_scale"]);
    $maxNewTokens = intval($GLOBALS["TTS"]["ZONOS"]["max_new_tokens"]);
    $timeout = intval($GLOBALS["TTS"]["ZONOS"]["timeout"]);
    $characterName = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : null;
    
    // Voice selection - exact same pattern as XTTS
    $voice = isset($GLOBALS["TTS"]["FORCED_VOICE_DEV"]) ? $GLOBALS["TTS"]["FORCED_VOICE_DEV"] : $GLOBALS["TTS"]["ZONOS"]["voiceid"];
    if (empty($voice)) {
        $voice = $GLOBALS["TTS"]["ZONOS"]["voiceid"];
    }
    
    // Load voice data from JSON file if it exists (same as XTTS)
    $voiceJsonPath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "{$voice}.json";
    if (file_exists($voiceJsonPath)) {
        $data_voice = json_decode(file_get_contents($voiceJsonPath), true);
        $speakerVoice = isset($data_voice['speaker_voice']) ? $data_voice['speaker_voice'] : $GLOBALS["TTS"]["ZONOS"]["speaker_voice"];
        $GLOBALS["DEBUG_DATA"][] = "Loaded voice from JSON: $voice -> $speakerVoice";
    } else {
        $speakerVoice = $GLOBALS["TTS"]["ZONOS"]["speaker_voice"];
        $GLOBALS["DEBUG_DATA"][] = "No JSON for voice '$voice', using default: $speakerVoice";
    }
    
    // Check if speaker voice file exists
    $speakerVoiceFullPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $speakerVoice;
    if (!file_exists($speakerVoiceFullPath)) {
        $GLOBALS["DEBUG_DATA"][] = "WARNING: Speaker voice file not found: $speakerVoiceFullPath";
    } else {
        $GLOBALS["DEBUG_DATA"][] = "Speaker voice file exists: $speakerVoiceFullPath";
    }
    
    try {
        
        if (!isZonosServiceAvailable($endpoint)) {
            return false;
        }
        
        // Convert relative path to absolute path for Zonos service
        $absoluteSpeakerVoice = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $speakerVoice;
        $absoluteSpeakerVoice = realpath($absoluteSpeakerVoice); // Normalize the path
        
        // Prepare batch request
        $requestData = [
            'sentences' => $sentences,
            'speaker_voice' => $absoluteSpeakerVoice,
            'language' => $language,
            'chunk_schedule' => $chunkSchedule,
            'chunk_overlap' => $chunkOverlap,
            'cfg_scale' => $cfgScale,
            'max_new_tokens' => $maxNewTokens,
            'streaming' => true,
            'character_name' => $characterName
        ];
        
        $audioData = generateBatchStreamingTTS($endpoint, $requestData, $timeout);
        
        if ($audioData === false) {
            return false;
        }
        
        // Save to cache
        $cachePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "soundcache/" . md5(trim($stringforhash)) . ".wav";
        file_put_contents($cachePath, $audioData);
        
        $totalTime = microtime(true) - $startTime;
        $GLOBALS["DEBUG_DATA"][] = $totalTime . " seconds in Zonos batch TTS call";
        
        return "soundcache/" . md5(trim($stringforhash)) . ".wav";
        
    } catch (Exception $e) {
        $GLOBALS["DEBUG_DATA"][] = "Zonos batch TTS error: " . $e->getMessage();
        return false;
    }
}

/**
 * Generate batch TTS with sentence-level streaming
 */
function generateBatchStreamingTTS($endpoint, $requestData, $timeout) {
    $url = rtrim($endpoint, '/') . '/tts/batch';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($requestData),
            'timeout' => $timeout
        ]
    ]);
    
    $handle = fopen($url, 'r', false, $context);
    
    if (!$handle) {
        return false;
    }
    
    // Parse mixed stream (audio chunks + sentence markers)
    $audioChunks = [];
    $buffer = '';
    
    while (!feof($handle)) {
        $data = fread($handle, 8192);
        if ($data === false) break;
        
        $buffer .= $data;
        
        // Process complete messages in buffer
        while (strlen($buffer) >= 10) { // Minimum message size
            if (substr($buffer, 0, 6) === 'AUDIO:') {
                // Audio chunk
                $length = unpack('V', substr($buffer, 6, 4))[1];
                if (strlen($buffer) >= 10 + $length) {
                    $audioData = substr($buffer, 10, $length);
                    $audioChunks[] = $audioData;
                    $buffer = substr($buffer, 10 + $length);
                } else {
                    break; // Wait for more data
                }
            } elseif (substr($buffer, 0, 9) === 'SENTENCE:') {
                // Sentence boundary marker
                $length = unpack('V', substr($buffer, 9, 4))[1];
                if (strlen($buffer) >= 13 + $length) {
                    $sentence = substr($buffer, 13, $length);
                    // Could log sentence completion here
                    $buffer = substr($buffer, 13 + $length);
                } else {
                    break; // Wait for more data
                }
            } else {
                // Skip unknown data
                $buffer = substr($buffer, 1);
            }
        }
    }
    
    fclose($handle);
    
    if (empty($audioChunks)) {
        return false;
    }
    
    // Combine all audio chunks
    $rawAudioData = implode('', $audioChunks);
    return convertRawToWav($rawAudioData);
}

?>
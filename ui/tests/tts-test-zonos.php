<?php

$localPath = dirname((__FILE__)) . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
require_once($localPath . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($localPath . "tts".DIRECTORY_SEPARATOR."tts-zonos.php");

error_reporting(E_ALL);

// Delete TTS cache for clean test
$directory = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."soundcache".DIRECTORY_SEPARATOR; 

$handle = opendir($directory);
if ($handle) {
	while (false !== ($file = readdir($handle))) {
		$filePath = $directory . DIRECTORY_SEPARATOR . $file;

		if (is_file($filePath)) {
			@unlink($filePath);//Deleting cache $filePath;
		}
	}
	closedir($handle);
}

$testString="In Skyrim's land of snow and ice, Where dragons soar and souls entwine, Heroes rise, their fate unveiled, As ancient tales, the land does bind.";
$mood="";

echo "<h2>Zonos Streaming TTS Test</h2>";
echo "<p><strong>Configuration:</strong></p>";
echo "<ul>";
echo "<li>Endpoint: " . htmlspecialchars($GLOBALS["TTS"]["ZONOS"]["endpoint"]) . "</li>";
echo "<li>Model: " . htmlspecialchars($GLOBALS["TTS"]["ZONOS"]["model"]) . "</li>";
echo "<li>Streaming Enabled: " . ($GLOBALS["TTS"]["ZONOS"]["streaming_enabled"] ? "Yes" : "No") . "</li>";
echo "<li>Language: " . htmlspecialchars($GLOBALS["TTS"]["ZONOS"]["language"]) . "</li>";
echo "</ul>";

// Check if Zonos service is available
$serviceAvailable = isZonosServiceAvailable($GLOBALS["TTS"]["ZONOS"]["endpoint"]);
if (!$serviceAvailable) {
    echo "<div style='color: red; font-weight: bold;'>⚠️ Zonos service not available at " . htmlspecialchars($GLOBALS["TTS"]["ZONOS"]["endpoint"]) . "</div>";
    echo "<p>Please ensure:</p>";
    echo "<ul>";
    echo "<li>The Zonos streaming service is running: <code>python3 zonos_streaming_service.py</code></li>";
    echo "<li>Or use the start script: <code>./start_zonos_service.sh</code></li>";
    echo "<li>The endpoint URL is correct in your configuration</li>";
    echo "</ul>";
    exit;
}

echo "<div style='color: green; font-weight: bold;'>✅ Zonos service is available</div>";

// Test streaming TTS
echo "<h3>Testing Streaming TTS</h3>";
$startTime = microtime(true);
$file = tts($testString, $mood, $testString);
$endTime = microtime(true);
$processingTime = round($endTime - $startTime, 2);

if ($file) {
	echo "<p><strong>Processing Time:</strong> {$processingTime} seconds</p>";
	echo "<p><strong>Test Text:</strong> " . htmlspecialchars($testString) . "</p>";
	echo "<audio controls style='width: 100%;'>
	<source src='../../$file' type='audio/wav'>
	Your browser does not support the audio element.
	</audio>";
	
	// Display debug info if available
	if (isset($GLOBALS["DEBUG_DATA"]) && !empty($GLOBALS["DEBUG_DATA"])) {
		echo "<h4>Debug Information:</h4>";
		echo "<pre>" . htmlspecialchars(print_r($GLOBALS["DEBUG_DATA"], true)) . "</pre>";
	}
	
} else {
	echo "<div style='color: red;'><strong>Error occurred:</strong></div>";
	$errorFile = __DIR__.DIRECTORY_SEPARATOR.".." . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR . "soundcache" . DIRECTORY_SEPARATOR.md5(trim($testString)) . ".err";
	if (file_exists($errorFile)) {
		echo "<pre>" . htmlspecialchars(file_get_contents($errorFile)) . "</pre>";
	}
}

// Test batch streaming if function exists
if (function_exists('ttsZonosBatch')) {
    echo "<h3>Testing Batch Streaming TTS</h3>";
    $sentences = [
        "Welcome to the world of Skyrim.",
        "Dragons have returned to the land.",
        "Your adventure begins now."
    ];
    
    $batchStartTime = microtime(true);
    $batchFile = ttsZonosBatch($sentences, $mood, implode(" ", $sentences));
    $batchEndTime = microtime(true);
    $batchProcessingTime = round($batchEndTime - $batchStartTime, 2);
    
    if ($batchFile) {
        echo "<p><strong>Batch Processing Time:</strong> {$batchProcessingTime} seconds</p>";
        echo "<p><strong>Sentences:</strong></p>";
        echo "<ul>";
        foreach ($sentences as $sentence) {
            echo "<li>" . htmlspecialchars($sentence) . "</li>";
        }
        echo "</ul>";
        echo "<audio controls style='width: 100%;'>
        <source src='../../$batchFile' type='audio/wav'>
        Your browser does not support the audio element.
        </audio>";
    } else {
        echo "<div style='color: red;'><strong>Batch test failed</strong></div>";
    }
}

?>
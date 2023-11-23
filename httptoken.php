<?php
require_once 'include/vendor/autoload.php';
loadEnv();
function loadEnv()
{
    $envFilePath = __DIR__ . '/.env';
    // Check if the .env file exists
    if (!file_exists($envFilePath)) {
        die('.env file not found. Please create one.');
    }

    // Read .env file contents
    $envFileContents = file_get_contents($envFilePath);
    $lines = explode("\n", $envFileContents);

    foreach ($lines as $line) {
        // Ignore comments and empty lines
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        // Parse the line and set environment variables
        list($key, $value) = explode('=', $line, 2);

        // Set environment variable
        if (putenv("$key=$value")) {
            // Define constant
            $constantName = strtoupper(str_replace(' ', '_', $key));
            defined($constantName) or define($constantName, getenv($key));

            // echo "</br> $constantName --- Configured...\n";
        } else {
            echo "Error configuring $key...\n";
        }
    }
}

$botToken = TGBOTTOKEN;
$webhookUrl = WEBHOOKURL;

// TODO configura webhook mediante http
$apiUrl = "https://api.telegram.org/bot$botToken/setWebhook?url=$webhookUrl";
$response = file_get_contents($apiUrl);

if ($response === false) {
    $error = error_get_last();
    echo "Error " . $error['message'];

} else {
    $responseData = json_decode($response, true);
    if ($responseData['ok'] == true) {
        echo "OK en la config";
    } else {
        echo "Error en la config";
    }
}




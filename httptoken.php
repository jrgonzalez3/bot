<?php
//TODO token de acceso 
$botToken = '6543217813:AAHVEbzoOWBmniWlu1vrQcgwk0_az3ElUgs';
$webhookUrl = 'https://jringenieriayservicios.com/bot/index.php';

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




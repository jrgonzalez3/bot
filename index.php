<?php
use TelegramBot\Api\BotApi;

require_once 'include/vendor/autoload.php';

//TODO token de acceso 
$botToken = '6543217813:AAHVEbzoOWBmniWlu1vrQcgwk0_az3ElUgs';
$webhookUrl = 'https://jringenieriayservicios.com/bot/index.php';

$telegram = new BotApi($botToken);

$update = json_decode(file_get_contents('php://input'));

if (isset($update->message->text)) {
    $chatId = $update->message->chat->id;
    $text = $update->message->text;

    if ($text === '/start') {
        $bienvenida = "Hola, Bienvenido!, Gracias por estar aqui, estos son los comandos que puedes usar \n\n";
        $data = $bienvenida;
        $data .= "/menu - mostrar menu\n";

        $telegram->sendMessage($chatId, $data);

    } else if ($text === 'Hola') {
        $telegram->sendMessage($chatId, 'Hola Doctor');

    } else {
        $defaultMessage = "Mi no entender ese comando";
        $telegram->sendMessage($chatId, $defaultMessage);
    }

}
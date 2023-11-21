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
    $message = $update->message->text;

    switch ($message) {
        case '/start':
            $bienvenida = "Hola, Bienvenido!, Gracias por estar aqui, estos son los comandos que puedes usar \n\n";
            $data = $bienvenida;
            $data .= "/menu - Para mostrar el menu principal\n";
            $telegram->sendMessage($chatId, $data);
            break;
        case '/menu':
            $menu = "/menu - Para mostrar el menu principal\n";
            //   $menu = "/menu - Para mostrar el menu principal\n";
            $telegram->sendMessage($chatId, $menu);
            break;

        default:
            $defaultMessage = "Mi no entender ese comando";
            $telegram->sendMessage($chatId, $defaultMessage);
            break;


    }

}
<?php
use TelegramBot\Api\BotApi;

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
$telegram = new BotApi($botToken);
$update = json_decode(file_get_contents('php://input'));


if (isset($update->message->text)) {
    $chatId = $update->message->chat->id;
    $message = $update->message->text;

    // Expresión regular para limpiar y validar el comando "/insertar"
    $pattern = "/\/$mensaje\s+([0-9a-zA-Z]+)/";
    $matches = [];

    if (preg_match($pattern, $message, $matches)) {
        // La expresión regular coincide, $matches[1] contiene el valor limpio
        $cleanedValue = strtoupper(trim($matches[1]));

        // Ahora $cleanedValue contiene el valor convertido a mayúsculas y sin espacios al principio o al final
        echo "Valor limpio: $cleanedValue";
    } else {
        // El comando no coincide con el formato esperado
        echo "Comando no válido";
    }

    switch ($message) {
        case '/start':
            $bienvenida = "Hola, Bienvenido!, Gracias por estar aqui, estos son los comandos que puedes usar \n\n";
            $data = $bienvenida;
            $data .= "/menu - Para mostrar el menu principal\n";
            $telegram->sendMessage($chatId, $data);
            break;
        case '/menu':
            $menu = "/menu - Este es el menu principal\n";
            $menu .= "/Consultaruc - Para mostrar consultar el Ruc\n";
            $telegram->sendMessage($chatId, $menu);
            break;
        case '/Consultaruc':
            $data = consultarWS($nrodocumento);
            $telegram->sendMessage($chatId, $data);
            break;

        default:
            $defaultMessage = "Mi no entender ese comando";
            $telegram->sendMessage($chatId, $defaultMessage);
            break;


    }





}



function consultarWS($nrodocumento)
{
    if ($nrodocumento > 0) {
        $url = URLAPIFS . '/ruc/' . $nrodocumento;
        $resp = httpPost($url, APIKEYFS);
        echo trim($resp);
    } else {
        echo json_encode(
            array(
                "Status" => false,
                "textStatus" => "Error Consulta",
            )
        );
    }
}

function httpPost($url, $apikey)
{
    $curl = curl_init();
    if (ENVIRONMENT != 'production') {
        $ssl = false;
    } else {

        $ssl = true;
    }

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => "$url",
            CURLOPT_SSL_VERIFYPEER => $ssl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer api_key_" . $apikey,
            ),
        )
    );
    $response = curl_exec($curl);
    if ($response === false) {
        $error = curl_error($curl);
        $errorCode = curl_errno($curl);
        echo "CURL Error: $error (Error Code: $errorCode)";
    }
    curl_close($curl);
    return $response;
}





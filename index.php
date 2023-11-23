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
            $data .= $bienvenida . "  /Consultaruc - Para mostrar consultar el Ruc\n";
            $telegram->sendMessage($chatId, $data);
            break;

        case '/ruc':
            $telegram->sendMessage($chatId, 'Por favor, introduce el número de RUC sin dígito verificador.');

            // Esperar la siguiente respuesta del usuario
            break;

        case '/ruc2':
            $bot->command('status', function ($message) use ($bot, $mysqli) {
                // Expresión regular para limpiar y validar el comando "/insertar"
                $pattern = "/\/ruc(\s+([0-9a-zA-Z]+))?/";
                $matches = [];

                if (preg_match($pattern, $message, $matches)) {
                    // El comando coincide, $matches[2] contiene el posible argumento
                    $argument = isset($matches[2]) ? strtoupper(trim($matches[2])) : null;

                    if ($argument !== null && !isValidNumber($argument)) {
                        $telegram->sendMessage($chatId, 'Número de documento no válido.');
                    } else {
                        // Si hay un argumento, consultar el webservice con ese argumento
                        $data = ($argument !== null) ? consultarWS($argument) : 'Consulta sin argumentos.';

                        // Enviar la respuesta al usuario
                        $telegram->sendMessage($chatId, $data);
                    }
                } else {
                    // El comando no coincide con el formato esperado
                    $telegram->sendMessage($chatId, 'Comando no válido. Utiliza /Consultaruc seguido de un número o texto.');
                }
            $data = consultarWS($nrodocumento);
                if (empty($data)) {
                    $data = 'Timer is not started.';
                }
                $bot->sendMessage($message->getChat()->getId(), $data);
            });
            $telegram->sendMessage($chatId, $data);
            break;
        case '/info':
            $infoMessage = "Justo González\nURL: [jrgonzalez3.github.io](https://jrgonzalez3.github.io)";
            $telegram->sendMessage($chatId, $infoMessage, 'markdown');
            break;
        default:
            $defaultMessage = "Lo siento, no entendí ese comando. Puedes usar /menu para ver las opciones disponibles.";
            $telegram->sendMessage($chatId, $defaultMessage);
            break;



    }
} elseif (isset($update->message->reply_to_message)) {
    // Si se recibe una respuesta al mensaje anterior (solicitando el RUC)
    $chatId = $update->message->chat->id;
    $message = $update->message->text;

    // Expresión regular para extraer el número de RUC sin dígito verificador
    $pattern = "/([0-9]+)/";
    $matches = [];

    if (preg_match($pattern, $message, $matches)) {
        $rucNumber = $matches[1];

        // Realizar la consulta al webservice con el número de RUC
        $data = consultarWS($rucNumber);

        // Enviar la respuesta al usuario
        $telegram->sendMessage($chatId, $data);
    } else {
        $telegram->sendMessage($chatId, 'Número de RUC no válido. Por favor, inténtalo de nuevo.');
    }

}

function isValidNumber($nrodocumento)
{
    // Realiza aquí cualquier validación adicional que puedas necesitar
    // Por ejemplo, longitud mínima, solo números, etc.
    return is_numeric($nrodocumento) && $nrodocumento > 0;
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





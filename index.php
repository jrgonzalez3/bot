<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'include/vendor/autoload.php';
use TelegramBot\Api\Client;

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
        } else {
            echo "Error configuring $key...\n";
        }
    }
}

$botToken = TGBOTTOKEN;
$webhookUrl = WEBHOOKURL;

try {
    $bot = new Client($botToken);

    $bot->command('about', function ($message) use ($bot) {
        $chatId = $message->getChat()->getId();
        $infoMessage = "Justo González\nURL: [jrgonzalez3.github.io](https://jrgonzalez3.github.io)";
        $bot->sendMessage($chatId, $infoMessage, null, true, null, null, 'markdown');
    });

    $bot->command('ruc', function ($message) use ($bot) {
        $chatId = $message->getChat()->getId();
        $bot->sendMessage($chatId, 'Por favor, introduce el número de RUC sin dígito verificador.');
        // Esperar la siguiente respuesta del usuario
    });

    $bot->on(function ($update) use ($bot) {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = trim($message->getText());

        // Expresión regular para extraer el número de RUC sin dígito verificador
        $pattern = "/([0-9]+)/";
        $matches = [];

        if (preg_match($pattern, $text, $matches)) {
            $rucNumber = $matches[1];

            // Realizar la consulta al webservice con el número de RUC
            $data = consultarWS($rucNumber);

            // Enviar la respuesta al usuario
            $bot->sendMessage($chatId, $data);
        } else {
            $bot->sendMessage($chatId, 'Número de RUC no válido. Por favor, inténtalo de nuevo.');
        }
    }, function ($message) {
        return true; // Este handler siempre se ejecuta
    });

    $bot->run();
} catch (Exception $e) {
    // Obtén el chat ID directamente de la actualización del mensaje
    echo 'Error: ' . $e->getMessage();
}

function isValidNumber($nrodocumento)
{
    return is_numeric($nrodocumento) && $nrodocumento > 0;
}

function consultarWS($nrodocumento)
{
    if ($nrodocumento > 0) {
        $url = URLAPIFS . '/ruc/' . $nrodocumento;
        $resp = httpPost($url, APIKEYFS);
        return trim($resp);
    } else {
        return json_encode([
            "Status" => false,
            "textStatus" => "Error Consulta",
        ]);
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
?>

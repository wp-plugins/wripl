<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

$wriplWP = WriplWP::$instance;

if (!$wriplWP->isSetup()) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit('wripl doesn\'t seem to be set up yet');
}

$wriplDebugData['wriplWpVersion'] = WriplWP::VERSION;


if (isset($_POST['debug'])) {
    $wriplDebugData['wriplSetup'] = (bool) $wriplWP->isSetup();
    $wriplDebugData['wordpressVersion'] = $wp_version;
    $wriplDebugData['curlVersion'] = function_exists('curl_version') ? curl_version() : false;
    $wriplDebugData['phpVersion'] = phpversion();
    $wriplDebugData['server'] = $_SERVER['SERVER_SOFTWARE'];
}

header('Content-Type: application/json');
echo json_encode($wriplDebugData);
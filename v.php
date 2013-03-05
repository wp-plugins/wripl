<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

$wriplWP = WriplWP::$instance;

if (!$wriplWP->isSetup()) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit('wripl doesn\'t seem to be set up yet');
}

$data['wriplWpVersion'] = $wriplWP::VERSION;


if (isset($_POST['debug'])) {
    $data['wriplSetup'] = (bool) $wriplWP->isSetup();
    $data['wordpressVersion'] = $wp_version;
    $data['curlVersion'] = function_exists('curl_version') ? curl_version() : false;
    $data['phpVersion'] = phpversion();
    $data['server'] = $_SERVER['SERVER_SOFTWARE'];
}

header('Content-Type: application/json');
echo json_encode($data);
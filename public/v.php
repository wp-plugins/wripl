<?php

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if (!WriplWordpress_Plugin::isSetup()) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit('wripl doesn\'t seem to be set up yet');
}

$wriplDebugData['wriplWpVersion'] = WriplWordpress_Plugin::VERSION;


if (isset($_POST['debug'])) {
    $wriplDebugData['wriplSetup'] = (bool) WriplWordpress_Plugin::isSetup();
    $wriplDebugData['wordpressVersion'] = $wp_version;
    $wriplDebugData['curlVersion'] = function_exists('curl_version') ? curl_version() : false;
    $wriplDebugData['curl_exec'] = function_exists('curl_exec');
    $wriplDebugData['phpVersion'] = phpversion();
    $wriplDebugData['server'] = $_SERVER['SERVER_SOFTWARE'];
}

header('Content-Type: application/json');
echo json_encode($wriplDebugData);
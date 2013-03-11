<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
require_once dirname(__FILE__) . '/WriplTokenStore.php';

WriplTokenStore::deleteAccessToken();

header("Location: {$_SERVER['HTTP_REFERER']}");

?>
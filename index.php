<?php

/**
 * Autoload
 */
require_once 'bootstrap.php';
/**
 * Net lib SSH2 SFTP
 */
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

include('Net/SSH2.php');

include('Net/SFTP.php');
/**
 * Max API config
 */
use max_api\api\maxApi;

/**
 * Start app
 */
$app = new maxApi();
/**
 * Initiiate Library
 */
$app->processApi();

<?php
/**
 * Autoload
 */
require_once 'bootstrap.php';

/**
 * Max API config
 */
use max_api\api\maxApi;

$app = new maxApi();

/**
 * Initiiate Library
 */
$app->processApi();

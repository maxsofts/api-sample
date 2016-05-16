<?php
/**
 * Autoload
 */
require_once 'bootstrap.php';

/**
 * Max API config
 */
use max_api\api\config;

$app = new config();

/**
 * Initiiate Library
 */
$app->processApi();

<?php
require('../vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function createLogger($logFileName) {
    date_default_timezone_set('Asia/Colombo');
    $logger = new Logger($logFileName);
    $logFile = '../logs/' . $logFileName;
    $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
    return $logger;
}
?>

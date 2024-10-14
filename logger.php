<?php

require __DIR__ . '/vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create a logger instance with a channel name
$logger = new Logger('my_logger');

// Add a StreamHandler to write logs to a file (e.g., `app.log` in the same directory)
$logger->pushHandler(new StreamHandler(__DIR__ . '../logs/app.log', Logger::DEBUG));
// Log an info message
$logger->info('This is an informational message.');

// Log a warning message
$logger->warning('This is a warning message.');

// Log an error message
$logger->error('This is an error message.');

?>


<?php
function errorHandler($errno, $errstr, $errfile, $errline) {
    $logger = createLogger('app.log');
    if ($logger) {
        $logger->critical("Error [$errno]: $errstr in $errfile on line $errline");
    }
    echo '
    <h1>Something went wrong</h1>
    <p>We are experiencing technical difficulties. Please try again later.</p>
    ';
    exit;
}
function shutdownHandler() {
    $error = error_get_last();
    if ($error) {
        $logger = createLogger('app.log');
        if ($logger) {
            $logger->critical("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
        }
        echo '
        <h1>Something went wrong</h1>
        <p>We are experiencing technical difficulties. Please try again later.</p>
        ';
        exit; 
    }
}
set_error_handler('errorHandler');
register_shutdown_function('shutdownHandler');
?>
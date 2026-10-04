<?php
# issue: errors dont save to file. Its in local error or in production It will work? 
function exceptionLog($text, $file) : void {
    $logDir = __DIR__ . '/../logs/';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . 'index.log';
    $message = date('Y-m-d H:i:s') . " - " . $text . " IN: " . $file . PHP_EOL;
    
    file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
}
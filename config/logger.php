<?php
function databaseLog($text, $file) {
    $logDir = __DIR__ . '/../logs/critical/';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . 'database.log';
    $message = date('Y-m-d H:i:s') . " - " . $text . " IN: " . $file . PHP_EOL;
    
    file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
}
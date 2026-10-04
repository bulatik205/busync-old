<?php
function getBackPath($fromDir) : string {
    $root = realpath($_SERVER['DOCUMENT_ROOT']);
    $from = realpath($fromDir);
    
    $root = str_replace('\\', '/', $root);
    $from = str_replace('\\', '/', $from);
    
    if (strpos($from, $root) !== 0) {
        return '/'; 
    }
    
    $relative = str_replace($root, '', $from);
    
    $relative = ltrim($relative, '/');
    
    if (empty($relative)) {
        return './';
    }
    
    $depth = substr_count($relative, '/');
    
    if ($depth == 0 && !empty($relative)) {
        $depth = 1;
    }
    
    return str_repeat('../', $depth);
}
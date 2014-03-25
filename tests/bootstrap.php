<?php
// turn on all errors
error_reporting(E_ALL);

// autoloader
spl_autoload_register(function ($class) {
    
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class)
          . '.php';

    $src = dirname(__DIR__) . DIRECTORY_SEPARATOR
         . 'src' . DIRECTORY_SEPARATOR
         . $file;

    if (is_readable($src)) {
        require $src;
        return;
    }

    $tests = dirname(__DIR__) . DIRECTORY_SEPARATOR
           . 'tests' . DIRECTORY_SEPARATOR
           . 'src' . DIRECTORY_SEPARATOR
           . $file;

    if (is_readable($tests)) {
        require $tests;
        return;
    }
});

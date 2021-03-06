<?php

spl_autoload_register(function($class) {
    require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php';
});

use \Michelf\Markdown;

function markdown_file($dir, $file) {
    $text = file_get_contents($dir . $file);
    $html = Markdown::defaultTransform($text);

    return $html;
}

function markdown($text) {
    $html = Markdown::defaultTransform($text);

    return $html;
}

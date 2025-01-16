<?php

// scoper-autoload.php @generated by PhpScoper

// Backup the autoloaded Composer files
if (isset($GLOBALS['__composer_autoload_files'])) {
    $existingComposerAutoloadFiles = $GLOBALS['__composer_autoload_files'];
}

$loader = require_once __DIR__.'/autoload.php';
// Ensure InstalledVersions is available
$installedVersionsPath = __DIR__.'/composer/InstalledVersions.php';
if (file_exists($installedVersionsPath)) require_once $installedVersionsPath;

// Restore the backup
if (isset($existingComposerAutoloadFiles)) {
    $GLOBALS['__composer_autoload_files'] = $existingComposerAutoloadFiles;
} else {
    unset($GLOBALS['__composer_autoload_files']);
}

// Class aliases. For more information see:
// https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md#class-aliases
if (!function_exists('humbug_phpscoper_expose_class')) {
    function humbug_phpscoper_expose_class(string $exposed, string $prefixed): void {
        if (!class_exists($exposed, false) && !interface_exists($exposed, false) && !trait_exists($exposed, false)) {
            spl_autoload_call($prefixed);
        }
    }
}
humbug_phpscoper_expose_class('ComposerAutoloaderInitf5b1e4936490b7cafde623f75cadbe4d', 'PHPStanBodyscan202501\ComposerAutoloaderInitf5b1e4936490b7cafde623f75cadbe4d');
humbug_phpscoper_expose_class('Normalizer', 'PHPStanBodyscan202501\Normalizer');

// Function aliases. For more information see:
// https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md#function-aliases
if (!function_exists('lintFile')) { function lintFile() { return \PHPStanBodyscan202501\lintFile(...func_get_args()); } }
if (!function_exists('normalizer_is_normalized')) { function normalizer_is_normalized() { return \PHPStanBodyscan202501\normalizer_is_normalized(...func_get_args()); } }
if (!function_exists('normalizer_normalize')) { function normalizer_normalize() { return \PHPStanBodyscan202501\normalizer_normalize(...func_get_args()); } }
if (!function_exists('scanPath')) { function scanPath() { return \PHPStanBodyscan202501\scanPath(...func_get_args()); } }
if (!function_exists('setproctitle')) { function setproctitle() { return \PHPStanBodyscan202501\setproctitle(...func_get_args()); } }
if (!function_exists('trigger_deprecation')) { function trigger_deprecation() { return \PHPStanBodyscan202501\trigger_deprecation(...func_get_args()); } }

return $loader;

<?php

/**
 * Vercel Entry Point for Laravel
 *
 * Vercel's filesystem is read-only except for /tmp.
 * This file sets up the correct storage paths before bootstrapping Laravel.
 */

// Set writable paths to /tmp for Vercel serverless environment
$storage_path = '/tmp/storage';

// Create necessary directories in /tmp if they don't exist
$dirs = [
    $storage_path,
    $storage_path . '/app',
    $storage_path . '/app/public',
    $storage_path . '/framework',
    $storage_path . '/framework/cache',
    $storage_path . '/framework/cache/data',
    $storage_path . '/framework/sessions',
    $storage_path . '/framework/views',
    $storage_path . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

// Override Laravel storage path to /tmp
$_ENV['APP_STORAGE_PATH'] = $storage_path;
putenv('APP_STORAGE_PATH=' . $storage_path);

require __DIR__ . '/../public/index.php';
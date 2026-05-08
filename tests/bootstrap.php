<?php

/**
 * Test bootstrap. Populates $_ENV / $_SERVER from the OS env vars set by
 * phpunit.xml's <php><env> block, so Laravel's Dotenv (which reads $_ENV
 * in immutable mode) sees the test overrides BEFORE loading .env.
 *
 * Without this, .env's DB_DATABASE=forerent wins over phpunit.xml's
 * forerent_testing override, and tests destroy the dev database.
 */

foreach (['APP_ENV', 'DB_DATABASE', 'BCRYPT_ROUNDS', 'CACHE_STORE', 'SESSION_DRIVER',
          'QUEUE_CONNECTION', 'MAIL_MAILER', 'APP_MAINTENANCE_DRIVER',
          'PULSE_ENABLED', 'TELESCOPE_ENABLED'] as $key) {
    $value = getenv($key);
    if ($value !== false) {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

require __DIR__ . '/../vendor/autoload.php';

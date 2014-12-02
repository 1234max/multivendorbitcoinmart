<?php

/**
 * holds all configuration data.
 *
 * reasons the error reporting is that way: @see http://www.phptherightway.com/#error_reporting
 */

# dev env
if(true) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    ini_set("log_errors", 1);

    define('BITCOIND_URL', 'http://bitcoinrpc:1234@127.0.0.1:28332');
    define('BITCOIN_ADMIN_PK', '022710e6fd81b88079fa1f1ca969e4244ab50c64d6c96858a814b26a20ba58c610');

    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'scam');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('PRODUCTION', false);
    date_default_timezone_set('UTC');
}
# prod env
else {
    error_reporting(E_ALL);
    ini_set("display_errors", 0);
    ini_set("display_startup_errors", 0);
    ini_set("log_errors", 1);

    define('BITCOIND_URL', 'http://bitcoinrpc:HIGHSECURE@127.0.0.1:28332');
    define('BITCOIN_ADMIN_PK', 'INSERT_BITCOIN_ADMIN_PUBLIC_KEY_HERE');

    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'scam');
    define('DB_USER', 'not-root');
    define('DB_PASS', 'mysql');
    define('PRODUCTION', true);
    date_default_timezone_set('UTC');
}
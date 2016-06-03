<?php


# dev env
if(true) {
    
    
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    ini_set("log_errors", 1);
    define('Commission', '5');
    define('BITCOIND_URL', 'http://bitcoinrpcusr:bitcoinrpcpasswd@127.0.0.1:8332');
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'mvbm');
    define('DB_USER', 'mvbm');
    define('DB_PASS', ' ');
    define('PRODUCTION', false);
    date_default_timezone_set('UTC');
    
}
# prod env
else {
    
    
    error_reporting(E_ALL);
    ini_set("display_errors", 0);
    ini_set("display_startup_errors", 0);
    ini_set("log_errors", 1);
    define('Commission', '5');
    define('BITCOIND_URL', 'http://bitcoinrpcusr:bitcoinrpcpasswd@127.0.0.1:8332');
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'mvbm');
    define('DB_USER', 'mvbm');
    define('DB_PASS', ' ');
    define('PRODUCTION', true);
    date_default_timezone_set('UTC');
    
}

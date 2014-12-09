<?php

chdir(realpath(dirname(__FILE__)));

require '../app/config/error_handling.php';
require '../app/config/config.php';

require '../app/lib/app.php';
require '../app/lib/model.php';

function getModel($modelName, $db) {
    require_once '../app/model/' . lcfirst($modelName) . '.php';
    $className = 'Scam\\' . $modelName . 'Model';
    return new $className($db);
}


/* used to initialize the database with admin bip32 key (used for multsig addresses)
 * and admin address (bitcoin auth for admin backend */
function setAdmin($app, $bip32Key, $adminAddress) {
    $db = $app->openDatabaseConnection();

    # validate bip32 key
    $key = getModel('User', $db)->parseBip32ExtendedPK($bip32Key);
    if(!$key || $key['depth'] != "2") {
        die("BIP32 key is not a valid bip32 key or not M/'k/0.\n");
    }

    # validate admin addresss
    if(!getModel('BitcoinTransaction', $db)->isValidBitcoinAddress($adminAddress)) {
        die("Address $adminAddress is not a valid bitcoin address.\n");
    }

    $configModel = getModel('Config', $db);
    if(!$configModel->setAdmin($bip32Key, $adminAddress)) {
        die("Error while saving to database.\n");
    }
}

/* Gets called by bitcoind everytime a new block is added.
 * Saves all transactions in this blocks in the database for later processal (by 'run')
 * */
function blockNotify($app, $block) {
    $db = $app->openDatabaseConnection();
    $bitcoinTransactionModel = getModel('BitcoinTransaction', $db);
    $bitcoinTransactionModel->addNewBlock($block);
}

/* Gets called as a cronjob periodically.
 * Processes all new transactions (saved by blockNotify) and checks if:
 *  - a payment to a multisig address has been made (order is paid by buyer)
 *  - a payment from the multisig address to the vendor has been made (buyer signed & broadcasted transaction = order is finished)
 * and then updates the order states accordingly.
 * */
function run($app) {
    $db = $app->openDatabaseConnection();
    $configModel = getModel('Config', $db);
    # implement locking so no more than one cronjob processes the transactions at a time
    $configModel->tryLock();
    try {
        $bitcoinTransactionModel = getModel('BitcoinTransaction', $db);
        $bitcoinTransactionModel->checkTransactionsForOrderPayments();
        $bitcoinTransactionModel->checkIfOrdersArePaid();
        $configModel->releaseLock();
    }
    catch(\Exception $e) {
        $configModel->releaseLock();
        throw $e;
    }
}

try {
    $app = new Scam\App();

    if(isset($argv[1])) {
        if($argv[1] == 'run') {
            run($app);
        }
        elseif($argv[1] == 'block-notify') {
            if(isset($argv[2])) {
                blockNotify($app, $argv[2]);
            }
            else {
                throw new Exception('block-notify without argument (block) called.');
            }
        }
        elseif($argv[1] == 'set-admin') {
            if(isset($argv[2]) && isset($argv[3])) {
                setAdmin($app, $argv[2], $argv[3]);
            }
            else {
                throw new Exception('set-admin without all arguments (extended public key, admin address) called.');
            }
        }
        else {
            throw new Exception('Unknown action ' . $argv[1]);
        }
    }
    else {
        throw new Exception('No action given.');
    }
}
catch(\Exception $e){
    print "Error: " . $e->getMessage() ."\n";
    print $e->getTraceAsString();
    exit(1);
}
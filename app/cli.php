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
    # todo: locking
    $db = $app->openDatabaseConnection();
    $bitcoinTransactionModel = getModel('BitcoinTransaction', $db);
    $bitcoinTransactionModel->checkTransactionsForOrderPayments();
    $bitcoinTransactionModel->checkIfOrdersArePaid();
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
        else {
            throw new Exception('Unknown action ' . $argv[1]);
        }
    }
    else {
        throw new Exception('No action given.');
    }
}
catch(\Exception $e){
    die("Error: " . $e->getMessage() . "\n");
}
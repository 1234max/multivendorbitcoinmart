# SCAM (Scammers Can't Acquire Money)
Secure bitcoin market built on PHP (developed as part of my bachelor studies about hidden marketplaces).

![SCAM listings](https://github.com/MatthiasWinzeler/scam/wiki/images/scam.png)

It provides only a basic set of marketplace features, but offers a significant higher level of [security](https://github.com/MatthiasWinzeler/scam/wiki/Security-&-Design-decisions)
which would protect against a very strong adversary.

Plus, its bitcoin integration avoid the use of live wallets by using multisig transactions and BIP32 hierarchical keys. 
Neither valuable bitcoins nor user private keys are stored on the marketplace. 

Why PHP? We use a custom, tiny MVC framework on top of a stripped-down php-core to keep the attack surface minimal.
See [Security & design decisions - PHP](https://github.com/MatthiasWinzeler/scam/wiki/Security-&-Design-decisions#php) for more.

## Features

* Basic 'shop' features (create products, shipping options, order lifecycle)
* Bitcoin multisig transactions & BIP32 keys
* PGP encryption of shipping info
* Simple admin interface (disputes etc.)

See [Features](https://github.com/MatthiasWinzeler/scam/wiki/Features) for more.

## Requirements

* Tested only on Linux for now, MAC OS X & other unices should work, too
* PHP 5.4+ (see Quickstart below) & MySQL 5+
* PHP dependencies as mentioned below (gnupg etc.) must compile on your platform
* ImageMagick (`identify`, `convert`, `mogrify` executables must be in `PATH`)
* Bitcoind

## Quickstart
*These instructions are only suited for a quick & dirty setup for developers!*

*If you're planning to run it in a productive environment, 
please see [Installation](https://github.com/MatthiasWinzeler/scam/wiki/Installation) in the wiki.*

### phpbrew (install php 5.4)
install phpbrew itself:

```bash
curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew
chmod +x phpbrew
sudo mv phpbrew /usr/bin/phpbrew
```

install latest php 5.4 with minimal extensions (pdo, mysql, multibyte and PGP only required for now):

```bash
phpbrew install 5.4 +pdo +mb +mysql +gnupg +gmp
```

### composer
install extensions needed for composer:

```bash
phpbrew ext install json
phpbrew ext install filter
phpbrew ext install hash
phpbrew ext install ctype
```

install composer

```bash
phpbrew install-composer
```

### Application
clone repo from github (requires git):

```bash
git clone https://github.com/MatthiasWinzeler/scam.git
cd scam
```

install dependencies using composer

```bash
composer install
```

install MySQL (add a dedicated user for scam), then init database with the provided scripts:

```bash
for sql_file in app/install/*.sql; do mysql -uroot -p < $sql_file; done
```

### bitcoind
Install [Bitcoind](https://bitcoin.org/en/download) and modify the bitcoin.conf to contain at least:
```
rpcuser=bitcoinrpc
rpcpassword=set a password here
# scam is currently only tested on bitcoin testnet:
testnet=1
blocknotify=/path/to/.phpbrew/php/php-5.4.34/bin/php /path/to/scam/app/cli.php block-notify %s
server=1
daemon=1
txindex=1
checkblocks=5
rpcport=28332
rpcconnect=127.0.0.1
```

```bitcoind``` will now notify SCAM for every new block seen on **testnet**. 
SCAM stores the received transactions of the block in the database for later handling (checking for payments etc.).  
This handling of transactions is done by another script that should be done periodically, i.e. with cron. Insert in your crontab:

```*/10 * * * * /path/to/.phpbrew/php/php-5.4.34/bin/php /path/to/scam/app/cli.php run```  

Or run the script manually to check the transactions.

Now run bitcoind:
```
bitcoind
```

### Configuration
Set the connection details for MySQL and bitcoind in `app/config/config.php`:
```php
define('BITCOIND_URL', 'http://bitcoinrpc:yourbitcoinpassword@127.0.0.1:28332');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'scam');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

At last, you have to define the admin bitcoin BIP32 extended public key M/k'/0 (used for multisig transactions - you can use [bip32.org](http://bip32.org/)) and a bitcoin address, whose private key you own (used for admin auth):

`php /path/to/scam/app/cli.php set-admin <BIP32_Extended_Public_Key_M/k'/0> <bitcoin-address>`

For example:

`php /path/to/scam/app/cli.php set-admin tpubDBvoSTTAJmqmqjkq5dPZLkk3rxMe4bdsJ1ZiKp4NkHh9xEf3yHqsNUfCZacdWLyejpFfqgRGQX1Moyd3xz2tpvfpYpRjeMbBwdiUKL6ccZi mpbbzJjE58afUMyS7MXnN9T4XaLQFM7dqX`

Now, you can run the server:

```bash
php -S localhost:3000
```

Access it with your webbrowser pointing to http://localhost:3000 or http://localhost:3000/?c=admin (admin interface)

## Developer notes
To debug, install xdebug and configure it for your favorite IDE:

```bash
phpbrew ext install xdebug stable
```

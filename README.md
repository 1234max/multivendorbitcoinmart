# MultiVendorBitcoinMart - secure anonymous multi vendor Bitcoin Market
Secure bitcoin market built on PHP 5.

Visit these sites to get an idea how good this really is !!!!

http://som.tor4.biz

https://cnpi7tv5e3spbhd2.onion/

http://multivendorbitcoinmarket.1234max.com

![MultiVendorBitcoinMart listings](https://1234max.co.uk/wp-content/uploads/2016/05/som.png)

The entry version provides only a basic set of marketplace features, but offers a significant higher level of [security](https://github.com/1234max/MultiVendorBitcoinMart/wiki/Security-&-Design-decisions)
which would protect against a very strong adversary.

Plus, its bitcoin implementation avoids the use of live wallets by using multisig transactions and BIP32 hierarchical keys. 
Neither valuable bitcoins nor user private keys are stored on the marketplace. 

Why PHP? We use a custom, tiny MVC framework on top of a stripped-down php-core to keep the attack surface minimal.
See [Security & design decisions - PHP](https://github.com/1234Max/MultiVendorBitcoinMart/wiki/Security-&-Design-decisions#php) for more.

## Features

* Basic 'shop' features (create products, shipping options, order lifecycle)
* Bitcoin multisig transactions & BIP32 keys
* PGP encryption of shipping info
* Simple admin interface (disputes etc.)

See [Features](https://github.com/1234max/MultiVendorBitcoinMart/wiki/Features) for more.

## Requirements

* Tested only on Linux for now, MAC OS X & other unices should work, too
* PHP 5.4+ (see Quickstart below) & Database Service (Like MySQL 5+,POstgreSQL,Etc)
* PHP dependencies as mentioned below (gnupg etc.) must compile on your platform
* ImageMagick (`identify`, `convert`, `mogrify` executables must be in `PATH`)
* Local or Remote Bitcoin RPC Service

## Quickstart
*These instructions are only suited for a quick & dirty setup for developers!*

*If you're planning to run it in a productive environment, 
please see [Installation](https://github.com/1234max/MultiVendorBitcoinMart/wiki/Installation) in the wiki.*

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
git clone https://github.com/1234max/MultiVendorBitcoinMart.git
cd MultiVendorBitcoinMart
```

install dependencies using composer

```bash
composer install
```

install MySQL (add a dedicated user for MultiVendorBitcoinMart), then init database with the provided scripts:

```bash
for sql_file in app/install/*.sql; do mysql -uroot -p < $sql_file; done
```

### bitcoind
Install [Bitcoind](https://bitcoin.org/en/download) and modify the bitcoin.conf to contain at least:
```
rpcuser=bitcoinrpc
rpcpassword=set a password here
# MultiVendorBitcoinMart is currently only tested on bitcoin testnet:
testnet=1
blocknotify=/path/to/.phpbrew/php/php-5.4.34/bin/php /path/to/MultiVendorBitcoinMart/app/cli.php block-notify %s
server=1
daemon=1
txindex=1
checkblocks=5
rpcport=28332
rpcconnect=127.0.0.1
```

```bitcoind``` will now notify MultiVendorBitcoinMart for every new block seen on **testnet**. 
MultiVendorBitcoinMart stores the received transactions of the block in the database for later handling (checking for payments etc.).  
This handling of transactions is done by another script that should be done periodically, i.e. with cron. Insert in your crontab:

```*/10 * * * * /path/to/.phpbrew/php/php-5.4.34/bin/php /path/to/MultiVendorBitcoinMart/app/cli.php run```  

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
define('DB_NAME', 'MultiVendorBitcoinMart');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

At last, you have to define the admin bitcoin BIP32 extended public key M/k'/0 (used for multisig transactions - you can use [bip32.1234max.com](http://bip32.1234max.com/)) and a bitcoin address, whose private key you own (used for admin auth):

`php /path/to/MultiVendorBitcoinMart/app/cli.php set-admin <BIP32_Extended_Public_Key_M/k'/0> <bitcoin-address>`

For example:

`php /path/to/MultiVendorBitcoinMart/app/cli.php set-admin xpub661MyMwAqRbcEbqhv4d3h7Ly1yLGH7PEYfdsnyT7aHMoJo5MFZxPmSku1qDSkYSz252ZDrkNYkwAeHcY5eEGvtw2JVJqYEr1m4Key77hUSu 1Mza1rMdL84coX4JBrzrGK9c612vsswcy9`

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
### Donations are welcome at 1KF8mSUQM4MoauiaoEa9AeWfQvZYujmGHr ###

Please do consult, if you want to set up or have this framwork setup by us.

Please goto http://1234max.co.uk/som to contact me and an up to date version of this software !

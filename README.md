# SCAM (Scammers Can't Acquire Money)
Secure bitcoin market built on PHP.

It offers only a basic set of marketplace features, but offers a significant higher level of [security](https://github.com/MatthiasWinzeler/scam/wiki/Security-&-Design-decisions)
which would be required when run as a darknet market.

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
* ImageMagick (convert & mogrify executables must be in path)
* Bitcoind

## Quickstart
For more thorough installation instructions or if you're planning to run it in a productive environment, 
please see [Installation](https://github.com/MatthiasWinzeler/scam/wiki/Installation) in the wiki.

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

### application
clone repo from github (requires git):

```bash
git clone https://github.com/MatthiasWinzeler/scam.git
cd scam
```

install dependencies using composer

```bash
composer install
```

install mysql, then init database with the provided scripts:

```bash
for sql_file in app/install/*.sql; do mysql -uroot -p < $sql_file; done
```

run server:

```bash
php -S localhost:3000
```

Access it with your webbrowser pointing to http://localhost:3000

## Developer notes
To debug, install xdebug and configure it for your favorite IDE:

```bash
phpbrew ext install xdebug stable
```

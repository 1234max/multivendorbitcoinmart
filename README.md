# MultiVendorBitcoinMart - secure anonymous multi vendor Bitcoin Market
Secure bitcoin market built on PHP 5.

Visit these sites to get an idea how good this really is !!!!


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

CREATE DATABASE IF NOT EXISTS `mvbm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mvbm`;



DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `admin_bip32_extended_public_key` text NOT NULL,
  `admin_bip32_key_index` int(2) NOT NULL,
  `admin_bitcoin_address` text NOT NULL,
  `permissions` int(2) NOT NULL,
  `isModerator` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `bitcoin_payments`;
CREATE TABLE IF NOT EXISTS `bitcoin_payments` (
  `address` varchar(35) NOT NULL,
  `tx_id` varchar(64) NOT NULL,
  `value` decimal(65,30) NOT NULL,
  `vout` int(11) NOT NULL,
  `pk_script` varchar(150) NOT NULL,
  PRIMARY KEY (`address`,`tx_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



DROP TABLE IF EXISTS `bitcoin_transactions`;
CREATE TABLE IF NOT EXISTS `bitcoin_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tx_id` varchar(64) NOT NULL,
  `raw_tx` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tx_id` (`tx_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `captchas`;
CREATE TABLE IF NOT EXISTS `captchas` (
  `code` varchar(5) NOT NULL,
  `image` mediumblob,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `iso` char(3) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`iso`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `entry_payment`;
CREATE TABLE IF NOT EXISTS `entry_payment` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(25) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `time` varchar(20) NOT NULL,
  `bitcoin_address` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user_hash`),
  KEY `user_hash_2` (`user_hash`,`bitcoin_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(65,30) NOT NULL,
  `amount` int(11) NOT NULL,
  `shipping_info` text,
  `finish_text` text,
  `buyer_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `shipping_option_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `buyer_public_key` varchar(66) DEFAULT NULL,
  `buyer_key_index` int(9) DEFAULT NULL,
  `buyer_refund_address` varchar(35) DEFAULT NULL,
  `vendor_public_key` varchar(66) DEFAULT NULL,
  `vendor_key_index` int(9) DEFAULT NULL,
  `vendor_payout_address` varchar(35) DEFAULT NULL,
  `admin_public_key` varchar(66) DEFAULT NULL,
  `admin_key_index` int(9) DEFAULT NULL,
  `multisig_address` varchar(35) DEFAULT NULL,
  `redeem_script` varchar(500) DEFAULT NULL,
  `unsigned_transaction` text,
  `partially_signed_transaction` text,
  `dispute_message` text,
  `dispute_unsigned_transaction` text,
  `dispute_signed_transaction` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `multisig_address` (`multisig_address`),
  KEY `buyer_id` (`buyer_id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `product_id` (`product_id`),
  KEY `shipping_option_id` (`shipping_option_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `payout_address`;
CREATE TABLE IF NOT EXISTS `payout_address` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `address` varchar(40) NOT NULL,
  `user_id` int(9) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(65,30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tags` text NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(12) NOT NULL,
  `image` mediumblob,
  `Category` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `products_shipping_options`;
CREATE TABLE IF NOT EXISTS `products_shipping_options` (
  `product_id` int(11) NOT NULL,
  `shipping_option_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`shipping_option_id`),
  KEY `shipping_option_id` (`shipping_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `hash` varchar(25) NOT NULL,
  `name` varchar(40) NOT NULL,
  `parent_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `hash_2` (`hash`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `shipping_options`;
CREATE TABLE IF NOT EXISTS `shipping_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(65,30) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_pin_hash` varchar(255) NOT NULL,
  `is_vendor` tinyint(1) NOT NULL DEFAULT '0',
  `pgp_public_key` text,
  `bip32_extended_public_key` varchar(300) DEFAULT NULL,
  `bip32_key_index` int(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `vendor_feedbacks`;
CREATE TABLE IF NOT EXISTS `vendor_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rating` tinyint(1) DEFAULT NULL,
  `comment` text,
  `order_id` int(11) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

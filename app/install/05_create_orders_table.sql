USE scam;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT 0,
  `price` decimal(65,30) NOT NULL,
  `amount` int(11) NOT NULL,
  `shipping_info` text,
  `finish_text` text,
  `buyer_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `product_id` int(11),
  `shipping_option_id` int(11),
  # timestamp trick from: http://gusiev.com/2009/04/update-and-create-timestamps-with-mysql/
  created_at timestamp default '0000-00-00 00:00:00',
  updated_at timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  FOREIGN KEY (buyer_id) REFERENCES users(id),
  FOREIGN KEY (vendor_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE SET NULL,
  FOREIGN KEY (shipping_option_id) REFERENCES shipping_options(id) ON DELETE SET NULL,
  # bitcoin stuff
  `buyer_public_key` varchar(66),
  `buyer_key_index` int(9),
  `buyer_refund_address` varchar(35),
  `vendor_public_key` varchar(66),
  `vendor_key_index` int(9),
  `vendor_payout_address` varchar(35),
  `admin_public_key` varchar(66),
  `admin_key_index` int(9),
  `multisig_address` varchar(35) UNIQUE,
  `redeem_script` varchar(500),
  `unsigned_transaction` text,
  `partially_signed_transaction` text,
  `dispute_message` text,
  `dispute_unsigned_transaction` text,
  `dispute_signed_transaction` text
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

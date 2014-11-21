USE scam;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(65,30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tags` text NOT NULL,
  `is_hidden` BOOLEAN NOT NULL DEFAULT 0,
  `code` varchar(12) NOT NULL UNIQUE,
  `image` MEDIUMBLOB,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products_shipping_options` (
  `product_id` int(11) NOT NULL,
  `shipping_option_id` int(11) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (shipping_option_id) REFERENCES shipping_options(id) ON DELETE CASCADE,
  PRIMARY KEY (product_id, shipping_option_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4


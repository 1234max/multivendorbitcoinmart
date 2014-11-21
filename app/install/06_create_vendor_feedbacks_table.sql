USE scam;

CREATE TABLE IF NOT EXISTS `vendor_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `rating` TINYINT(1),
  `comment` text,
  `order_id` int(11),
  `buyer_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4

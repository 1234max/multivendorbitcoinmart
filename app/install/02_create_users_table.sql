USE scam;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `profile_pin_hash` varchar(255) NOT NULL,
  `is_vendor` BOOLEAN NOT NULL DEFAULT 0,
  `pgp_public_key` text
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4
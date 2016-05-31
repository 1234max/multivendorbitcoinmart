use mvbm;

CREATE TABLE IF NOT EXISTS `registration_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(100) NOT NULL,
  `user_type` enum('1','2','3') NOT NULL,
  `token_content` varchar(128) NOT NULL,
  `entry_payment` decimal(20,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `token_content` (`token_content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


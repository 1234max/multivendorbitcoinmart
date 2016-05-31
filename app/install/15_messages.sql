use mvbm;


CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `content` blob NOT NULL,
  `encrypted` enum('0','1') NOT NULL,
  `rsa_encrypted` enum('0','1') DEFAULT '0',
  `aes_iv` mediumblob,
  `aes_key` mediumblob,
  `hash` varchar(25) NOT NULL,
  `remove_on_read` enum('0','1') NOT NULL,
  `time` int(20) NOT NULL,
  `to` int(9) NOT NULL,
  `viewed` enum('0','1') NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores encrypted/plaintext messages.' AUTO_INCREMENT=1 ;


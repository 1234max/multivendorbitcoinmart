use mvbm;

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


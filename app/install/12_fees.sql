use mvbm;

CREATE TABLE IF NOT EXISTS `fees` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `low` decimal(20,8) NOT NULL COMMENT 'Orders exceeding this value apply to this range',
  `high` decimal(20,8) NOT NULL COMMENT 'Orders less than this value apply to this range',
  `rate` decimal(4,3) NOT NULL COMMENT 'Percentage fee to be charged for this range',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;



INSERT INTO `fees` (`id`, `low`, `high`, `rate`) VALUES
(1, 0.00000000, 0.50000000, 1.000),
(2, 0.50000000, 5.00000000, 0.750),
(3, 5.00000000, 500.00000000, 0.500);



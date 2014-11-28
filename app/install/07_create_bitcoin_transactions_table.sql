USE scam;

CREATE TABLE IF NOT EXISTS `bitcoin_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tx_id` varchar(64) NOT NULL UNIQUE,
  `raw_tx` text NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4

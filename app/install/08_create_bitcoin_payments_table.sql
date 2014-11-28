USE scam;

CREATE TABLE IF NOT EXISTS `bitcoin_payments` (
  `address` varchar(35) NOT NULL,
  `tx_id` varchar(64) NOT NULL,
  `value` decimal(65,30) NOT NULL,
  `vout` int NOT NULL,
  `pk_script` varchar(150) NOT NULL,
  PRIMARY KEY(`address`, `tx_id`), # avoid duplicate payments
  FOREIGN KEY (address) REFERENCES orders(multisig_address) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4

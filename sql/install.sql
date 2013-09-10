CREATE TABLE IF NOT EXISTS `civicrm_pledge_sepa_dd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pledge_id` int(11) NOT NULL,
  `pledge_payment_id` int(11) NOT NULL,
  `contribution_id` int(11) DEFAULT NULL,
  `seqtp` varchar(4) NOT NULL,
  `reqdcolltndt` date NOT NULL,
  `credttm` datetime DEFAULT NULL,
  `mdtid` varchar(255) NOT NULL,
  `dtofsgntr` date NOT NULL,
  `dbtr_iban` varchar(34) NOT NULL,
  `dbtr_bic` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` float NOT NULL,
  `amount_ccy` varchar(3) NOT NULL DEFAULT 'EUR',
  `cdtr_id` varchar(64) NOT NULL,
  `cdtr_iban` varchar(34) NOT NULL,
  `cdtr_bic` varchar(64) NOT NULL,
  `feedback_date` date DEFAULT NULL,
  `feedback_state` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `civicrm_pledges_sepa_dd_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creditor_name` varchar(70) CHARACTER SET latin1 NOT NULL,
  `creditor_id` varchar(64) CHARACTER SET latin1 NOT NULL,
  `creditor_iban` varchar(34) NOT NULL,
  `creditor_bic` varchar(34) NOT NULL,
  `transaction_id` varchar(35) CHARACTER SET latin1 NOT NULL,
  `transaction_date` date NOT NULL,
  `collection_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

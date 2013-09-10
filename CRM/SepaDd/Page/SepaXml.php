<?php

require_once 'CRM/Core/Page.php';

require_once 'lib/Pain008Generator.php';

class CRM_SepaDd_Page_SepaXml extends CRM_Core_Page {

	function run() {
		$transaction_date = new DateTime();
		$transaction_date->modify("+5 days");
		if ($transaction_date->format('N') >= 6) {
			$transaction_date->modify("next monday");
		}
		
		$data['creditor_name'] = sepa_dd_pledges_get_config_value('creditor_name');
		$data['creditor_id'] = sepa_dd_pledges_get_config_value('creditor_id');
		$data['creditor_iban'] = sepa_dd_pledges_get_config_value('creditor_iban');
		$data['creditor_bic'] = sepa_dd_pledges_get_config_value('creditor_bic');
		$data['transaction_date'] = $transaction_date;
		$data['collection_date'] = $transaction_date;
		
		$transactions = $this->insertNewTransaction($data);
		if ($transactions === false) {
			CRM_Core_Error::fatal("Could not create transaction file");
		}
		
		//get all pledges with status Overdue
		$this->getTransactions($transactions, 6);
		//get all pledges with status In Progress
		$this->getTransactions($transactions, 5);
		//get all pledges with status Pending
		$this->getTransactions($transactions, 2);
		
		header('Content-Type: text/xml; charset=utf-8');
		header('Content-Disposition: attachment; filename="sepa_'.$data['transaction_date']->format('dmY').'.pain008.xml"');
		$g = new Pain008Generator();
		echo $g->generate($transactions);
		exit();
	}
	
	private function getTransactions(Pain008Transactions $transactions, $status_id) {
		$seqtp_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'seqtp'));
		$custom_sepa_group = CRM_SepaDd_Utils_SepaDdUtils::retrieveCustomGroupByName('org_civicoop_sepa_dd_pledges');
		if ($custom_sepa_group == false) {
			CRM_Core_Error::fatal("Customgroup SEPA for pledges does not exist. Did you install the extension properly?");
		}
		
		$contribution_status_value = CRM_SepaDd_Utils_SepaDdUtils::getOptionValueByGroupAndName('contribution_status', 'Pending');
		if ($contribution_status_value==false) {
			CRM_Core_Error::fatal("Contribution status pending does not exist. Please contact your system administrator");
		}
		
		$payment_instrument = CRM_SepaDd_Utils_SepaDdUtils::getOptionValueByGroupAndName('payment_instrument', 'sepa');
		if ($payment_instrument==false) {
			CRM_Core_Error::fatal("Could not find SEPA payment instrument");
		}
	
		$pledge_params['pledge_status_id'] = $status_id;
		$pledge_params['version'] = 3;
		$pledges = civicrm_api('Pledge', 'get', $pledge_params);
		if (isset($pledges['values']) && is_array($pledges['values'])) {
			foreach($pledges['values'] as $pledge) {			
				if ($pledge['pledge_currency'] != 'EUR') {
					continue;
				}
				
				$sepa_values = CRM_SepaDd_Utils_SepaDdUtils::retrieveCustomValuesSorted($pledge['id'], 'Pledge', $custom_sepa_group['id']);
				
				$data = array();
				$data['dbtr_name'] = $pledge['display_name'];
				$data['seqtp'] = 'FRST';
				if (isset($sepa_values['latest']['seqtp'])) {
					$data['seqtp'] = $sepa_values['latest']['seqtp'];
				}
				if (isset($sepa_values['latest']['mdtid'])) {
					$data['mdtid'] = $sepa_values['latest']['mdtid'];
				} else {
					//no valid mandaat
					continue;
				}
				if (isset($sepa_values['latest']['dtofsgntr']) && $sepa_values['latest']['dtofsgntr']) {
					$data['dtofsgntr'] = new DateTime($sepa_values['latest']['dtofsgntr']);
					if ($data['dtofsgntr'] >= $transactions->transactionDate) {
						//signature date is after transaction date
						continue;
					}
				} else {
					//signature date missing
					continue;
				}
				if (isset($sepa_values['latest']['iban'])) {
					$data['dbtr_iban'] = $sepa_values['latest']['iban'];
				} else {
					//iban missing
					continue;
				}
				if (isset($sepa_values['latest']['bic'])) {
					$data['dbtr_bic'] = $sepa_values['latest']['bic'];
				} else {
					//bic missing
					continue;
				}
				
				//Only execute the euro transactions
				//Get the pledgepayment
				$pledge_payment = $this->getPledgePayment($transactions, $pledge['id'], 6); //get the payments (for status overdue)
				if ($pledge_payment === false) {
					$pledge_payment = $this->getPledgePayment($transactions, $pledge['id'], 2); //get the payments (for status pending)
				}
				if ($pledge_payment === false) {
					//no valid payment found
					continue;
				}
				
				//record as a contribution
				$contribution_params['context'] = 'pledge';
				$contribution_params['ppid'] = $plegde_payments['id'];
				$contribution_params['financial_type_id'] = $pledge['pledge_financial_type'];
				$contribution_params['contact_id'] = $pledge['contact_id'];
				$contribution_params['total_amount'] = $pledge_payment['scheduled_amount'];
				$contribution_params['instrument_id'] = 'sepa';
				$contribution_params['contribution_status_id'] = $contribution_status_value;
				$contribution_params['version'] = 3;
				$contribution_result = civicrm_api('Contribution', 'create', $contribution_params);
				$contribution = false;
				if (isset($contribution_result['values']) && is_array($contribution_result['values'])) {
					$contribution = reset($contribution_result['values']);
				}
				
				//contribution didn't get inserted
				if ($contribution === false) {
					continue;
				}
				
				//update pledge payment
				$update_pledge_payment['contribution_id'] = $contribution['id'];
				$update_pledge_payment['id'] = $plegde_payments['id'];
				$update_pledge_payment['pledge_id'] = $pledge['id'];
				$update_pledge_payment['actual_amount'] = $pledge_payment['scheduled_amount'];
				$update_pledge_payment['status_id'] = 1; //completed
				$update_pledge_payment['version'] = 3;
				$update_pledge_payment_result = civicrm_api('PledgePayment', 'create', $update_pledge_payment);
				$updated_pledge_payment = false;
				if (isset($update_pledge_payment_result['values']) && is_array($update_pledge_payment_result['values'])) {
					$updated_pledge_payment = reset($update_pledge_payment_result['values']);
				}
				
				//contribution didn't get inserted
				if ($updated_pledge_payment === false) {
					continue;
				}
				
				$t = $this->generateIndividualTransaction($pledge, $updated_pledge_payment, $contribution, $data, $transactions);
				if ($t !== false) {
					$transactions->transactions[] = $t;
					
					//update pledge sequence type status
					if (isset($seqtp_field['id'])) {
						$seqtp_params['entity_table'] = 'Pledge';
						$seqtp_params['entity_id'] = $pledge['id'];
						$seqtp_params['custom_'.$seqtp_field['id']] = 'RCUR';
						$seqtp_params['version'] = 3;
						civicrm_api('CustomValue', 'Create', $seqtp_params);
					}
				}
			}
		}
	}
	
	private function getPledgePayment(Pain008Transactions $transactions, $pledge_id, $status) {
		$pledge_payment_params['pledge_id'] = $pledge_id;
		$pledge_payment_params['status_id']  = $status;
		$pledge_payment_params['version'] = 3;
		$pledge_payments = civicrm_api('PledgePayment', 'get', $pledge_payment_params);
		$pledge_payment = false;
		if (isset($pledge_payments['values']) && is_array($pledge_payments['values'])) {
			$pledge_payment = reset($pledge_payments['values']);
			if ($pledge_payment) {
				$pay_date = new DateTime($pledge_payment['scheduled_date']);
				if ($pay_date > $transactions->transactionDate) {
					$pledge_payment = false;
				}
			}
		}
		return $pledge_payment;
	}
	
	private function generateIndividualTransaction($pledge, $pledge_payment, $contribution, $data, Pain008Transactions $transactions) {
		$sql = "INSERT INTO `civicrm_pledge_sepa_dd` (
			`pledge_id`,
			`pledge_payment_id`,
			`contribution_id`,
			`seqtp`,
			`reqdcolltndt`,
			`mdtid`,
			`dtofsgntr`,
			`dbtr_name`,
			`dbtr_iban`,
			`dbtr_bic`,
			`transaction_id`,
			`transaction_date`,
			`end_to_end_id`,
			`amount`,
			`amount_ccy` 
		) VALUES (
			'".$pledge['id']."',
			'".$pledge_payment['id']."',
			'".$contribution['id']."',
			'".$data['seqtp']."',
			'".$transactions->reqdcolltndt->format('Y-m-d')."',
			'".$data['mdtid']."',
			'".$data['dtofsgntr']->format('Y-m-d')."',
			'".$data['dbtr_name']."',
			'".$data['dbtr_iban']."',
			'".$data['dbtr_bic']."',
			'".$transaction->transactionId."',
			'".$transactions->transactionDate->format('Y-m-d')."',
			'".$transactions->transactionId.'-'.$contribution['id']."',
			'".$pledge_payment['actual_amount']."',
			'EUR'
		);";
		
		$dao = CRM_Core_DAO::executeQuery($sql);
		$id = mysql_insert_id(); //hack The DAO object has no method to retrieve the last insert ID. 
		
		$sql = "SELECT * FROM `civicrm_pledge_sepa_dd` WHERE `id` = '".$id."'";
		$dao = CRM_Core_DAO::executeQuery($sql);
		if ($dao->fetch($row)) {
			$t = new Pain008Transaction();
			$t->setFromArray($dao->toArray());
			return $t;
		}
		return false;
	}
  
	private function insertNewTransaction($data) {
		$sql = "INSERT INTO `civicrm_pledges_sepa_dd_transactions` (
			`creditor_name`,
			`creditor_id`,
			`creditor_iban`,
			`creditor_bic`,
			`transaction_date`,
			`collection_date`
		) VALUES (
			'".CRM_Core_DAO::escapeString($data['creditor_name'])."',
			'".CRM_Core_DAO::escapeString($data['creditor_id'])."',
			'".CRM_Core_DAO::escapeString($data['creditor_iban'])."',
			'".CRM_Core_DAO::escapeString($data['creditor_bic'])."',
			'".$data['transaction_date']->format('Y-m-d')."',
			'".$data['collection_date']->format('Y-m-d')."'
		);";
		
		$dao = CRM_Core_DAO::executeQuery($sql);
		$id = mysql_insert_id(); //hack The DAO object has no method to retrieve the last insert ID. 
		
		$sql = "UPDATE `civicrm_pledges_sepa_dd_transactions` SET `transaction_id` = '".CRM_Core_DAO::escapeString(date('Y').'-'.$id)."' WHERE `id` = '".$id."'";
		$dao = CRM_Core_DAO::executeQuery($sql);
		
		$sql = "SELECT * FROM `civicrm_pledges_sepa_dd_transactions` WHERE `id` = '".$id."'";
		$dao = CRM_Core_DAO::executeQuery($sql);
		$row = array();
		if ($dao->fetch($row)) {
			$t = new Pain008Transactions();
			$t->setFromArray($dao->toArray());
			return $t;
		}
		return false;
	}
  
  function getTestTransaction($txt_id) {
	$txt = new Pain008Transaction();
	
	$txt->id = 1;	
	$txt->pledge_id = 1;
	$txt->pledge_payment_id = 1;
	//$txt->contribution_id = null;
	$txt->seqtp = 'RCUR';
	$txt->reqdcolltndt = '2012-02-21';
	//$txt->credttm;
	$txt->mdtid = 'MANDAAT123456';
	$txt->dtofsgntr = new DateTime('2013-08-01');
	$txt->dbtr_name = 'FICO Customer account';
	$txt->dbtr_iban = 'NL60RABO0122046897';
	$txt->dbtr_bic = 'RABONL2U';
	$txt->amount = 1600.00;
	$txt->amount_ccy = 'EUR';
	//$txt->description;
	//$txt->cdtr_id;
	//$txt->cdtr_iban = 'DE12345678901234567890';
	//$txt->cdtr_bic = 'ABNADEFFFRA';
	$txt->transaction_id = $txt_id;
	$txt->transaction_date = new DateTime();
	$txt->end_to_end_id = $txt_id . '-'.$txt->id;
	//$this->feedback_date;
	//$this->feedback_state;
	
	return $txt;
  }
}
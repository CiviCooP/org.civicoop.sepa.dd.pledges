<?php

class Pain008Transactions {

	public $transactions = array();
	
	public $ccy = 'EUR';
	
	public $creditor_name;
	
	public $creditor_id;
	
	public $creditor_iban;
	
	public $creditor_bic;
	
	public $transactionId;
	
	public $transactionDate;
	
	public $reqdcolltndt;
	
	public function __construct($id='', $name='') {
		$this->transactionId = $id;
		$this->creditor_name = $name;
		$this->reqdcolltndt = new DateTime();
		$this->transactionDate = new DateTime();
	}
	
	public function getTransactionCount($type = false) {
		$transactionCount = 0;
		foreach($this->transactions as $transaction) {
			if ($type===false || $transaction->seqtp == $type) {
				if ($transaction->amount_ccy == $this->ccy) {
					$transactionCount ++;
				}
			}
		}
		return $transactionCount;
	}
	
	public function getSum($type = false) {
		$sum = 0.00;
		foreach($this->transactions as $transaction) {
			if ($type===false || $transaction->seqtp == $type) {
				if ($transaction->amount_ccy == $this->ccy) {
					$sum = $sum + $transaction->amount;
				}
			}
		}
		return $sum;
	}
	
	public function setFromArray($data) {
		if (isset($data['creditor_name'])) {
			$this->creditor_name = $data['creditor_name'];
		}		
		if (isset($data['creditor_id'])) {
			$this->creditor_id = $data['creditor_id'];
		}
		if (isset($data['creditor_iban'])) {
			$this->creditor_iban = $data['creditor_iban'];
		}
		if (isset($data['creditor_bic'])) {
			$this->creditor_bic = $data['creditor_bic'];
		}
		if (isset($data['transaction_id'])) {
			$this->transactionId = $data['transaction_id'];
		}
		if (isset($data['transaction_date'])) {
			$this->transactionDate = new DateTime($data['transaction_date']);
		}
		if (isset($data['collection_date'])) {
			$this->reqdcolltndt = new DateTime($data['collection_date']);
		}
	}

}
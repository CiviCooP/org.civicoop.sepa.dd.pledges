<?php

class Pain008Transaction {

	public $id;	
	public $pledge_id;
	public $pledge_payment_id;
	public $contribution_id;
	public $seqtp;
	public $reqdcolltndt;
	public $credttm;
	public $mdtid;
	public $dtofsgntr;
	public $dbtr_iban;
	public $dbtr_bic;
	public $dbtr_name;
	public $amount;
	public $amount_ccy;
	public $description;
	public $cdtr_id;
	public $cdtr_iban;
	public $cdtr_bic;
	public $end_to_end_id;
	public $transaction_id;
	public $transaction_date;
	public $feedback_date;
	public $feedback_state;
	
	public function setFromArray($array) {
		if (isset($array['id'])) {
			$this->id = $array['id'];
		}
		if (isset($array['pledge_id'])) {
			$this->pledge_id = $array['pledge_id'];
		}
		if (isset($array['pledge_payment_id'])) {
			$this->pledge_payment_id = $array['pledge_payment_id'];
		}
		if (isset($array['contribution_id'])) {
			$this->contribution_id = $array['contribution_id'];
		}
		if (isset($array['seqtp'])) {
			$this->seqtp = $array['seqtp'];
		}
		if (isset($array['reqdcolltndt'])) {
			$this->reqdcolltndt = $array['reqdcolltndt'];
		}
		if (isset($array['credttm'])) {
			$this->credttm = $array['credttm'];
		}
		if (isset($array['mdtid'])) {
			$this->mdtid = $array['mdtid'];
		}
		if (isset($array['dtofsgntr'])) {
			$this->dtofsgntr = new DateTime($array['dtofsgntr']);
		}
		if (isset($array['dbtr_iban'])) {
			$this->dbtr_iban = $array['dbtr_iban'];
		}
		if (isset($array['dbtr_name'])) {
			$this->dbtr_name = $array['dbtr_name'];
		}
		if (isset($array['dbtr_bic'])) {
			$this->dbtr_bic = $array['dbtr_bic'];
		}
		if (isset($array['amount'])) {
			$this->amount = $array['amount'];
		}
		if (isset($array['amount_ccy'])) {
			$this->amount_ccy = $array['amount_ccy'];
		}
		if (isset($array['description'])) {
			$this->description = $array['description'];
		}
		if (isset($array['cdtr_id'])) {
			$this->cdtr_id = $array['cdtr_id'];
		}
		if (isset($array['cdtr_iban'])) {
			$this->cdtr_iban = $array['cdtr_iban'];
		}
		if (isset($array['cdtr_bic'])) {
			$this->cdtr_bic = $array['cdtr_bic'];
		}
		if (isset($array['end_to_end_id'])) {
			$this->end_to_end_id = $array['end_to_end_id'];
		}
		if (isset($array['transaction_id'])) {
			$this->transaction_id = $array['transaction_id'];
		}
		if (isset($array['transaction_date'])) {
			$this->transaction_date = $array['transaction_date'];
		}
		if (isset($array['feedback_date'])) {
			$this->feedback_date = $array['feedback_date'];
		}
		if (isset($array['feedback_state'])) {
			$this->feedback_state = $array['feedback_state'];
		}
		
	}

}
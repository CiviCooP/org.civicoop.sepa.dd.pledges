<?php

require_once('Pain008Transaction.php');
require_once('Pain008Transactions.php');

class Pain008Generator {
	
	/**
	 * Generates an PAIN.008 xml file for SEPA Direct Debit
	 *
	 * @parameter $transactions Pain008Transactions info object with all the pain 008 transactions
	 */
	public function generate($transactions) {		
		$pain = new DOMDocument('1.0', 'UTF-8');
		
		$document = $pain->createElement('Document');
		$documentAttribute1 = $pain->createAttribute('xmlns');
		$documentAttribute1->value = 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02';
		$documentAttribute2 = $pain->createAttribute('xmlns:xsi');
		$documentAttribute2->value = 'http://www.w3.org/2001/XMLSchema-instance';
		
		$document->appendChild($documentAttribute1);
		$document->appendChild($documentAttribute2);
		$pain->appendChild($document);
		
		$CstmrDrctDbtInitn = $pain->createElement('CstmrDrctDbtInitn');
		$document->appendChild($CstmrDrctDbtInitn);
		
		$GrpHdr = $pain->createElement('GrpHdr');
		$CstmrDrctDbtInitn->appendChild($GrpHdr);
		$MsgId = $pain->createElement('MsgId', $transactions->transactionId);		
		$GrpHdr->appendChild($MsgId);
		$CreDtTm = $pain->createElement('CreDtTm', $transactions->transactionDate->format('c'));
		$GrpHdr->appendChild($CreDtTm);
		$NbOfTxs = $pain->createElement('NbOfTxs', $transactions->getTransactionCount());
		$GrpHdr->appendChild($NbOfTxs);
		$CtrlSum = $pain->createElement('CtrlSum', $this->number($transactions->getSum(), 2));
		$GrpHdr->appendChild($CtrlSum);
		$InitgPty = $pain->createElement('InitgPty');
		$GrpHdr->appendChild($InitgPty);
		$InitgPtyNm = $pain->createElement('Nm', $transactions->creditor_name);
		$InitgPty->appendChild($InitgPtyNm);
		
		if ($transactions->getTransactionCount('FRST')) {
			$CstmrDrctDbtInitn->appendChild($this->createPaymentInfo('FRST', $transactions, $pain));
		}
		if ($transactions->getTransactionCount('RCUR')) {
			$CstmrDrctDbtInitn->appendChild($this->createPaymentInfo('RCUR', $transactions, $pain));
		}
		
		return $pain->saveXML();
	}
	
	private function createPaymentInfo($type, Pain008Transactions $transactions, DOMDocument $pain) {
		$PmtInf = $pain->createElement('PmtInf');	

		$PmtInfId = $pain->createElement('PmtInfId', $transactions->transactionId);
		$PmtInf->appendChild($PmtInfId);
		$PmtMtd = $pain->createElement('PmtMtd', 'DD');
		$PmtInf->appendChild($PmtMtd);
		$PmtInfNbOfTxs = $pain->createElement('NbOfTxs', $transactions->getTransactionCount($type));
		$PmtInf->appendChild($PmtInfNbOfTxs);
		$PmtInfCtrlSum = $pain->createElement('CtrlSum', $this->number($transactions->getSum($type), 2));
		$PmtInf->appendChild($PmtInfCtrlSum);
		$PmtTpInf = $pain->createElement('PmtTpInf');
		$PmtInf->appendChild($PmtTpInf);
		$SvcLvl = $pain->createElement('SvcLvl');
		$PmtTpInf->appendChild($SvcLvl);
		$SvcLvlCd = $pain->createElement('Cd', 'SEPA');
		$SvcLvl->appendChild($SvcLvlCd);
		$LclInstrm = $pain->createElement('LclInstrm');
		$PmtTpInf->appendChild($LclInstrm);
		$LclInstrmCd = $pain->createElement('Cd', 'CORE');
		$LclInstrm->appendChild($LclInstrmCd);
		$SeqTp = $pain->createElement('SeqTp', $type);
		$PmtTpInf->appendChild($SeqTp);		
		$ReqdColltnDt = $pain->createElement('ReqdColltnDt', $transactions->reqdcolltndt->format('Y-m-d'));
		$PmtInf->appendChild($ReqdColltnDt);
		$Cdtr = $pain->createElement('Cdtr');
		$PmtInf->appendChild($Cdtr);
		$CdtrNm = $pain->createElement('Nm', $transactions->creditor_name);
		$Cdtr->appendChild($CdtrNm);
		$CdtrAcct = $pain->createElement('CdtrAcct');
		$PmtInf->appendChild($CdtrAcct);
		$CdtrAcctId = $pain->createElement('Id');
		$CdtrAcct->appendChild($CdtrAcctId);
		$CdtrAcctIban = $pain->createElement('IBAN', $transactions->creditor_iban);
		$CdtrAcctId->appendChild($CdtrAcctIban);
		//CCY under CrdtrAcct should not be used according to the ING test tool
		/*$CdtrAcctCcy = $pain->createElement('Ccy', $transactions->ccy);
		$CdtrAcct->appendChild($CdtrAcctCcy);*/
		
		$CdtrAgt = $pain->createElement('CdtrAgt');
		$PmtInf->appendChild($CdtrAgt);
		$FinInstnId = $pain->createElement('FinInstnId');
		$CdtrAgt->appendChild($FinInstnId);
		$CdtrAgtBic = $pain->createElement('BIC', $transactions->creditor_bic);
		$FinInstnId->appendChild($CdtrAgtBic);
		
		foreach($transactions->transactions as $t) {
			if ($t->seqtp == $type) {
				$DrctDbtTxInf = $this->getDrctDbtTxInf($t, $transactions, $pain);
				$PmtInf->appendChild($DrctDbtTxInf);
			}
		}
		
		return $PmtInf;
	}
	
	private function getDrctDbtTxInf(Pain008Transaction $t, Pain008Transactions $transactions, DOMDocument $dom) {
		$d = $dom->createElement('DrctDbtTxInf');
		
		$PmtId = $dom->createElement('PmtId');
		$d->appendChild($PmtId);
		$EndToEndId = $dom->createElement('EndToEndId', $t->end_to_end_id);
		$PmtId->appendChild($EndToEndId);
		
		$InstdAmt = $dom->createElement('InstdAmt', $this->number($t->amount, 2));
		$ccy = $dom->createAttribute('Ccy');
		$ccy->value = $t->amount_ccy;
		$InstdAmt->appendChild($ccy);
		$d->appendChild($InstdAmt);
		
		$DrctDbtTx = $dom->createElement('DrctDbtTx');
		$d->appendChild($DrctDbtTx);
		
		$MndtRltdInf = $dom->createElement('MndtRltdInf');
		$DrctDbtTx->appendChild($MndtRltdInf);
		
		$MndtId = $dom->createElement('MndtId', $t->mdtid);
		$MndtRltdInf->appendChild($MndtId);
		$DtOfSgntr = $dom->createElement('DtOfSgntr', $t->dtofsgntr->format('Y-m-d'));
		$MndtRltdInf->appendChild($DtOfSgntr);
		$AmdmntInd = $dom->createElement('AmdmntInd', 'false');
		$MndtRltdInf->appendChild($AmdmntInd);
		
		$CdtrSchmeId = $dom->createElement('CdtrSchmeId');
		$DrctDbtTx->appendChild($CdtrSchmeId);
		$CdtrSchemeIdId = $dom->createElement('Id');
		$CdtrSchmeId->appendChild($CdtrSchemeIdId);
		$CdtrPrvtId = $dom->createElement('PrvtId');
		$CdtrSchemeIdId->appendChild($CdtrPrvtId);
		$CdtrPrvtIdOthr = $dom->createElement('Othr');
		$CdtrPrvtId->appendChild($CdtrPrvtIdOthr);
		$CdtrPrvtIdOthrId = $dom->createElement('Id', $transactions->creditor_id);
		$CdtrPrvtIdOthr->appendChild($CdtrPrvtIdOthrId);
		$CdtrSchmeNm = $dom->createElement('SchmeNm');
		$CdtrPrvtIdOthr->appendChild($CdtrSchmeNm);
		$CdtrPrtry = $dom->createElement('Prtry', 'SEPA');
		$CdtrSchmeNm->appendChild($CdtrPrtry);
		
		$DbtrAgt = $dom->createElement('DbtrAgt');
		$d->appendChild($DbtrAgt);
		$FinInstnId = $dom->createElement('FinInstnId');
		$DbtrAgt->appendChild($FinInstnId);
		$DbtrAgtBic = $dom->createElement('BIC', $t->dbtr_bic);
		$FinInstnId->appendChild($DbtrAgtBic);
		
		$Dbtr = $dom->createElement('Dbtr');
		$d->appendChild($Dbtr);
		$DbtrNm = $dom->createElement('Nm', $t->dbtr_name);
		$Dbtr->appendChild($DbtrNm);
		
		$DbtrAcct = $dom->createElement('DbtrAcct');
		$d->appendChild($DbtrAcct);
		$DbtrAcctId = $dom->createElement('Id');
		$DbtrAcct->appendChild($DbtrAcctId);
		$DbtrAcctIban = $dom->createElement('IBAN', $t->dbtr_iban);
		$DbtrAcctId->appendChild($DbtrAcctIban);
		
		$Purp = $dom->createElement('Purp');
		$d->appendChild($Purp);
		$PurpCd = $dom->createElement('Cd', 'OTHR');
		$Purp->appendChild($PurpCd);
		
		return $d;
	}
	
	private function number($num) {
		return number_format($num, 2, '.','');
	}
}
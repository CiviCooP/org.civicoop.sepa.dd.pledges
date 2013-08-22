<?php

require_once 'CRM/Core/Page.php';

class CRM_SepaDd_Page_Mandate extends CRM_Core_Page {

	function run() {
		//dummy is this function needed?
	}
	
	function generate(&$pledge) {
		$sepa_group = civicrm_api('CustomGroup', 'getSingle', array('version'=>3, 'name' => 'sepa'));
		$iban_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'iban'));
		$mdtid_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'mdtid'));
		$mandate_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'mandate'));		
		
		$mdtid = $this->generateMandateNumber();
		$pledge['custom'][$mdtid_field['id']][-1]['value'] = $mdtid;
		$pledge['custom'][$mdtid_field['id']][-1]['id']  = null;
		$pledge['custom'][$mdtid_field['id']][-1]['type'] = $mdtid_field['data_type'];
		$pledge['custom'][$mdtid_field['id']][-1]['custom_field_id'] = $mdtid_field['id'];
		$pledge['custom'][$mdtid_field['id']][-1]['custom_group_id'] = $mdtid_field['custom_group_id'];
		$pledge['custom'][$mdtid_field['id']][-1]['table_name'] = $sepa_group['table_name'];
		$pledge['custom'][$mdtid_field['id']][-1]['column_name'] = $mdtid_field['column_name'];
		$pledge['custom'][$mdtid_field['id']][-1]['file_id'] = null;
		$pledge['custom'][$mdtid_field['id']][-1]['is_multiple'] = '0';		
		
		$iban = false;
		if (isset($pledge['custom'][$iban_field['id']][-1]['value'])) {
			$iban = $pledge['custom'][$iban_field['id']][-1]['value'];
		}
		
		if (isset($pledge['custom'][$mdtid_field['id']][-1]['value'])) {
			$mdtid = $pledge['custom'][$mdtid_field['id']][-1]['value'];
		}
		
		$contact = civicrm_api('Contact', 'getsingle', array('version' => 3, 'contact_id' => $pledge['contact_id']));
	
		$this->assign('iban', $iban);
		$this->assign('mandaat_id', $mdtid);
		
		$this->assign('contact', $contact);
		$this->assign('creditor_id', sepa_dd_pledges_get_config_value('creditor_id'));
		
		$mandate = $this->getTemplate()->fetch('mandate.tpl');
		$pledge['custom'][$mandate_field['id']][-1]['value'] = $mandate;
		$pledge['custom'][$mandate_field['id']][-1]['id']  = null;
		$pledge['custom'][$mandate_field['id']][-1]['type'] = $mandate_field['data_type'];
		$pledge['custom'][$mandate_field['id']][-1]['custom_field_id'] = $mandate_field['id'];
		$pledge['custom'][$mandate_field['id']][-1]['custom_group_id'] = $mandate_field['custom_group_id'];
		$pledge['custom'][$mandate_field['id']][-1]['table_name'] = $sepa_group['table_name'];
		$pledge['custom'][$mandate_field['id']][-1]['column_name'] = $mandate_field['column_name'];
		$pledge['custom'][$mandate_field['id']][-1]['file_id'] = null;
		$pledge['custom'][$mandate_field['id']][-1]['is_multiple'] = '0';
		
		
		require_once 'CRM/Utils/PDF/Utils.php';
		$fileName = $mdtid.".pdf";
		$config = CRM_Core_Config::singleton();
		$pdfFullFilename = $config->templateCompileDir . CRM_Utils_File::makeFileName($fileName);
		file_put_contents($pdfFullFilename, CRM_Utils_PDF_Utils::html2pdf( $mandate,$fileName, true, null ));
		
		list($domainEmailName,$domainEmailAddress) = CRM_Core_BAO_Domain::getNameAndEmail();
		$params              = array();
		$params['groupName'] = 'SEPA Email Sender';
		$params['from']      = '"' . $domainEmailName . '" <' . $domainEmailAddress . '>';
		$params['toEmail'] = $contact['email'];
		$params['toName']  = $params['toEmail'];
		
		if (empty ($params['toEmail'])){
			CRM_Core_Session::setStatus(ts("Error sending $fileName: Contact doesn't have an email."));
		}
		$params['subject'] = "SEPA Mandaat " . $mdtid;
		$params['attachments'][] = array(
			'fullPath' => $pdfFullFilename,
			'mime_type' => 'application/pdf',
			'cleanName' => $fileName,
		);
		
		
		$params['text'] = $this->getTemplate()->fetch("mandate_mail_text.tpl");
		$params['html'] = $this->getTemplate()->fetch("mandate_mail_html.tpl");
      
		CRM_Utils_Mail::send($params);
		CRM_Core_Session::setStatus(ts("Mail sent"));
	}
	
	function generateMandateNumber() {
		
		$sepa_group = civicrm_api('CustomGroup', 'getSingle', array('version'=>3, 'name' => 'sepa'));
		$mdtid_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'mdtid'));
		
		$mdtid = false;
		$sql = "SELECT (MAX(`id`) + 1) AS `mdtid` FROM `".$sepa_group['table_name']."`";
		$dao = CRM_Core_DAO::executeQuery($sql);
		$row = array();
		if ($dao->fetch($row)) {
			$data = $dao->toArray();
			if (isset($data['mdtid']) && $data['mdtid']) {
				$mdtid = $data['mdtid'];
			}
		}
		
		if ($mdtid === false) {
			$mdtid = 1;
		}
		
		return 'AMN-'.date('Y').'-'.$mdtid;
		
	}

}
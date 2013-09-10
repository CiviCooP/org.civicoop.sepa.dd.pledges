<?php

require_once("packages/php-iban-1.4.0/php-iban.php");

function sepa_dd_pledges_civicrm_navigationMenu(&$params) {	
	$maxKey = ( max(array_keys($params)) );
	$insert_at = min(4, max(array_keys($params)));
	$parent = 3;
	
	$sepa = array(
		'attributes' => array(
			'label' => 'SEPA Direct Debit',
			'name' => 'SEPA_DD',
			'url' => 'civicrm/sepa_dd/xml',
			'permission' => 'access CiviContribute',
			'operator' => null,
			'separator' => 0,
			'parentID' => null,
			'navID' => $parent,
			'active' => 1
		),
	);
	
	//find menu contributions 
	foreach($params as $mid => $menu) {
		if (isset($menu['attributes']) && isset($menu['attributes']['name']) && $menu['attributes']['name'] == 'Contributions') {
			$params[$mid]['child'][$insert_at] = $sepa;
			break;
		}
	}
	
	
	
	//print_r($params); exit();
}

function sepa_dd_pledges_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ){
	if ("CRM_Pledge_Form_Pledge"  == $formName) { 
		$iban_field_name = false;
		$bic_field_name = false;
		$gid = CRM_SepaDd_Utils_SepaDdUtils::retrieveCustomGroupByName('org_civicoop_sepa_dd_pledges');
		$iban_custom_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'iban', 'custom_group_id' => $gid));
		$bic_custom_field = civicrm_api('CustomField', 'getSingle', array('version'=>3, 'name' => 'bic', 'custom_group_id' => $gid));
		
		if (isset($iban_custom_field['id'])) {
			foreach($fields as $key => $field) {
				if (strpos($key, 'custom_'.$iban_custom_field['id'].'_')===0) {
					$iban_field_name = $key;
				}
			}
		}
		if (isset($bic_custom_field['id'])) {
			foreach($fields as $key => $field) {
				if (strpos($key, 'custom_'.$bic_custom_field['id'].'_')===0) {
					$bic_field_name = $key;
				}
			}
		}
		if ($iban_field_name !== false && array_key_exists($iban_field_name,$fields)) {
			if (!verify_iban($fields[$iban_field_name])) {
				$errors[$iban_field_name] = ts( 'invalid IBAN' );
			}
		}
		if ($bic_field_name !== false && array_key_exists($bic_field_name,$fields)) {
			// we use the same function that cleans iban to clean bic
			$fields[$bic_field_name] = iban_to_machine_format($fields[$bic_field_name]);
			if (!preg_match("/^[0-9a-z]{4}[a-z]{2}[0-9a-z]{2}([0-9a-z]{3})?\z/i", $fields[$bic_field_name])) {
				$errors[$bic_field_name] = ts( 'invalid BIC' );
			} 
		}
	}
}

function sepa_dd_pledges_civicrm_pre($op, $objectName, $id, &$params) {
	if ($objectName == 'Pledge' && $op == 'create') {
		$mandate = new CRM_SepaDd_Page_Mandate();
		$mandate->generate($params);
	}
	
}
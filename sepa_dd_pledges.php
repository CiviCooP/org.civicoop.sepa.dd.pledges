<?php
ini_set( 'display_errors', '1');
require_once 'sepa_dd_pledges.civix.php';
require_once 'hooks.php';

/** 
 * get a config value for a key. If the config does not exist it returns null
 */
function sepa_dd_pledges_get_config_value($key) {
	require 'config.php';
	if (isset($civicoop_config['org.civicoop.sepa.dd.pledges'][$key])) {
		return $civicoop_config['org.civicoop.sepa.dd.pledges'][$key];
	}
	return null;
}

/**
 * Implementation of hook_civicrm_config
 */
function sepa_dd_pledges_civicrm_config(&$config) {
  _sepa_dd_pledges_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function sepa_dd_pledges_civicrm_xmlMenu(&$files) {
  _sepa_dd_pledges_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function sepa_dd_pledges_civicrm_install() {  
  $sql = file_get_contents(dirname( __FILE__ ) .'/sql/install.sql', true);
  //CRM_Utils_File::sourceSQLFile($config->dsn, $sql, NULL, true);
  
  return _sepa_dd_pledges_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function sepa_dd_pledges_civicrm_uninstall() {
	return _sepa_dd_pledges_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function sepa_dd_pledges_civicrm_enable() { 
	 _sepa_dd_pledges_add_option_value('sepa', 'payment_instrument');
	 
	$gid = _sepa_dd_pledges_add_customgroup('sepa', 'SEPA Direct Debit', 'Pledge');
	if ($gid) {
		_sepa_dd_pledges_add_customfield($gid, 'seqtp', 'Sequence Type', 'String', 'Text', '1', 1, array (
			'default_value' => 'FRST',
			'is_view' => 1,
			'is_searchable' => 1,
		));
		_sepa_dd_pledges_add_customfield($gid, 'iban', 'IBAN', 'String', 'Text', '1', 1);
		_sepa_dd_pledges_add_customfield($gid, 'bic', 'BIC', 'String', 'Text', '1', 2);
		_sepa_dd_pledges_add_customfield($gid, 'mdtid', 'Mandaatnummer', 'String', 'Text', '1', 3, array(
			'is_view' => 1,
		));
		_sepa_dd_pledges_add_customfield($gid, 'mandate', 'Mandaat', 'Memo', 'RichTextEditor', '1', 4, array(
			'is_view' => 1,
		));
		_sepa_dd_pledges_add_customfield($gid, 'dtofsgntr', 'Datum ondertekening', 'Date', 'Select Date', '1', 5, array(
			'is_required' => 0,
		));
	}
 
	return _sepa_dd_pledges_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function sepa_dd_pledges_civicrm_disable() {
	_sepa_dd_pledges_delete_customgroup('sepa');
	return _sepa_dd_pledges_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function sepa_dd_pledges_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sepa_dd_pledges_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function sepa_dd_pledges_civicrm_managed(&$entities) {
  return _sepa_dd_pledges_civix_civicrm_managed($entities);
}

function _sepa_dd_pledges_add_customgroup($group, $group_title, $extends) {
	$params['version']  = 3;
	$params['name'] = $group;
	$result = civicrm_api('CustomGroup', 'getsingle', $params);
	if (!isset($result['id'])) {
		unset($params);
		$params['version']  = 3;
		$params['name'] = $group;
		$params['title'] = $group_title;
		$params['extends'] = $extends;
		$params['is_active'] = '1';
		$result = civicrm_api('CustomGroup', 'create', $params);
	}
	$gid = false;
	if (isset($result['id'])) {
		$gid = $result['id'];
	}
	
	return $gid;
} 

function _sepa_dd_pledges_add_customfield($gid, $name, $label, $data_type, $html_type, $active, $weight = 0, $attributes = array()) {
	
	$params['version']  = 3;
	$params['custom_group_id'] = $gid;
	$params['label'] = $label;
	$result = civicrm_api('CustomField', 'getsingle', $params);
	if (!isset($result['id'])) {
		unset($params);
		$params['version']  = 3;
		$params['custom_group_id'] = $gid;
		$params['name'] = $name;
		$params['label'] = $name;
		$params['html_type'] = $html_type;
		$params['data_type'] = $data_type;
		$params['is_active'] = $active;
		$params['weight'] = $weight;
		$params2 = array();
		foreach($attributes as $attr => $val) {
			if (!isset($params[$attr])) {
				$params2[$attr] = $val;
				$params[$attr] = $val;
			}
		}
		$result = civicrm_api('CustomField', 'create', $params);
		
		$params2['version'] = 3;
		$params2['label'] = $label;
		$params2['is_active'] = $active;
		$params2['id'] = $result['id'];
		
		civicrm_api('CustomField', 'create', $params2);
	}
}

function _sepa_dd_pledges_delete_customgroup($name) {
	$params['version']  = 3;
	$params['name'] = $name;
	$result = civicrm_api('CustomGroup', 'getsingle', $params);
	if (isset($result['id'])) {
		$gid = $result['id'];
		unset($params);
		$params['version']  = 3;
		$params['custom_group_id'] = $gid;
		$result = civicrm_api('CustomField', 'get', $params);
		if (isset($result['values']) && is_array($result['values'])) {
			foreach($result['values']  as $field) {
				unset($params);
				$params['version']  = 3;
				$params['id'] = $field['id'];
				civicrm_api('CustomField', 'delete', $params);
			}
		}
	
		unset($params);
		$params['version']  = 3;
		$params['id'] = $gid;
		$result = civicrm_api('CustomGroup', 'delete', $params);
	}
}

function _sepa_dd_pledges_enable_customgroup($name, $enable) {
  $params['version']  = 3;
  $params['name'] = $name;
  $result = civicrm_api('CustomGroup', 'getsingle', $params);
  if (isset($result['id'])) {
	$gid = $result['id'];
	unset($params);
	$params['version']  = 3;
	$params['id'] = $gid;
	$params['is_active'] = $enable ? '1' : '0';
	$result = civicrm_api('CustomGroup', 'update', $params);
  }
}

function _sepa_dd_pledges_add_option_value($name, $option_group_name) {
	$civiparms = array('version' => 3, 'name' => $option_group_name);
	$civires = civicrm_api('OptionGroup', 'getsingle', $civiparms);
	if (civicrm_error($civires)) {
		return;
	}
	
	//check if value exists
	$civiparms2['version'] = 3;
	$civiparms2['option_group_id'] = $civires['id'];
	$civiparms2['name'] = $name;
	$civires2 = civicrm_api('OptionValue', 'getsingle', $civiparms2);
	if (!civicrm_error($civires2)) {
		return;
	}
	
	//create value;
	$civires3 = civicrm_api('OptionValue', 'Create', $civiparms2);
	return;
}

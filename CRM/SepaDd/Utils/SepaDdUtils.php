<?php
/*
+--------------------------------------------------------------------+
| Project       :   Sepa Direct Debit (pledges)                      |
| Author        :   Jaap Jansma (CiviCooP, jaap.jansma@civicoop.org  |
| Date          :   14 August 2013                                   | 
| Description   :   Class with helper functions for the              |
|                   Sepa Direct Debit                                |
+--------------------------------------------------------------------+
*/

/**
*
* @package CRM
* @copyright CiviCRM LLC (c) 2004-2013
* $Id$
*
*/
class CRM_SepaDd_Utils_SepaDdUtils {

	public static function retrieveCustomGroupByName($name) {
		$civiparms2 = array('version' => 3, 'name' => $name);
		$civires2 = civicrm_api('CustomGroup', 'getsingle', $civiparms2);
		$id = false;
		if (!civicrm_error($civires2)) {
			return $civires2;
		}
		return false;
	}
	
	public static function getOptionValueByGroupAndName($option_group_name, $name) {
		$option = CRM_SepaDd_Utils_SepaDdUtils::getOptionByGroupAndName($option_group_name, $name);
		if ($option === false) {
			return false;
		}
		return $option['value'];
	}
	
	public static function getOptionIdByGroupAndName($option_group_name, $name) {
		$option = CRM_SepaDd_Utils_SepaDdUtils::getOptionByGroupAndName($option_group_name, $name);
		if ($option === false) {
			return false;
		}
		return $option['id'];
	}
	
	public static function getOptionByGroupAndName($option_group_name, $name) {
		$civiparms = array('version' => 3, 'name' => $option_group_name);
		$civires = civicrm_api('OptionGroup', 'getsingle', $civiparms);
		if (civicrm_error($civires)) {
			return false;
		}
		$civiparms2['version'] = 3;
		$civiparms2['option_group_id'] = $civires['id'];
		$civiparms2['name'] = $name;
		$civires2 = civicrm_api('OptionValue', 'getsingle', $civiparms2);
		if (civicrm_error($civires2)) {
			return false;
		}
		return $civires2;
	}

	public static function retrieveCustomValuesSorted($entity_id, $entity_table, $group_id) {
		$customValues = CRM_SepaDd_Utils_SepaDdUtils::retrieveCustomValues($entity_id, $entity_table, $group_id);
		$fields = array();
		if (isset($customValues['values']) && is_array($customValues['values'])) {
			foreach($customValues['values'] as $values) {
				foreach($values as $key => $v) {
					$k = (string) $key;
					if ($k != 'entity_id' && $k != 'id' && $k != 'name' && $k != 'entity_table') {
						$fields[$key][$values['name']] = $v;
					}
				}
			}
		}
		return $fields;
	}

	public static function retrieveCustomValues($entity_id, $entity_table, $group_id) {
		$return['is_error'] = '0';
		$params = array(
				'version' => 3,
				'sequential' => 1,
				'entity_id' => $entity_id,
				'entity_table' => $entity_table,
		);
		$values = civicrm_api('CustomValue', 'get', $params);
		if (isset($values['is_error']) && $values['is_error'] == '1') {
			return $values;
		}

		$i = 0;
		foreach($values['values'] as $value) {
			$params = array(
					'version' => 3,
					'sequential' => 1,
					'id' => $value['id'],
					'custom_group_id' => $group_id
			);
			$fields = civicrm_api('CustomField', 'getsingle', $params);
			if (!isset($fields['is_error'])) {
				$return['values'][$i] = $value;
				$return['values'][$i]['name'] = $fields['name'];
				$i++;
			}
		}
		return $return;
	}

}
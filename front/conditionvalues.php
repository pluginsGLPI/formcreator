<?php
	include ('../../../inc/includes.php');
	
	if(isset($_GET['id'])) {
		$conditionValuesResult = $DB->request([
			'SELECT' => ['default_values', 'glpi_plugin_formcreator_questions.values'],
			'FROM'   => 'glpi_plugin_formcreator_questions',
			'WHERE'  => [
				'id' => $_GET['id']
			]
		])->next();
		$conditionValues = $conditionValuesResult['values'];
		$conditionValuesNames = $conditionValuesResult['default_values'];
		$conditionValuesArray = preg_split("/\\r\\n/", $conditionValues);
		$conditionValuesNamesArray = preg_split("/\\r\\n/", $conditionValuesNames);
		$i = 0;
		$dropdown_condition_values = [];
		foreach ($conditionValuesArray as $conditionValue) {
			$dropdown_condition_values[$conditionValue] = $conditionValuesNamesArray[$i];
			$i = $i + 1;
		}
		Dropdown::showFromArray('condition_value', $dropdown_condition_values);
	}

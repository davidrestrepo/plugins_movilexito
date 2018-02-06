<?php
namespace Drupal\movilexito_ws\Report;

class Tools{
	public static function filter_query(){
		if(empty($_SESSION['movilexito_ws']['report_filter'])){
			return;
		}

		$fields = array();
		$values = array();
		foreach ($_SESSION['movilexito_ws']['report_filter'] as $arg => $filter) {
			$name_field = $filter['field'];
			if($name_field == 'emsl.fecha_actualizacion'){
				$filter['value'] = date('Y-m-d H:s:i', $filter['value']);
			}
			$fields[] = "($name_field ".$filter['op']." :$arg)";
			$values[":$arg"] = $filter['value'];
		}

		return array(
			'where' => implode(' AND ', $fields),
			'args' => $values
		);
	}

	public static function get_distinct_values($field, $empty = false){
		$types = array();
		if($empty){
			$types[] = 'Todos';
		}
		$db = \Drupal::database();
		$result = $db->query('SELECT DISTINCT('.$field.') as field FROM {exito_me_ws_log} ORDER BY field');
		foreach ($result as $object) {
			$types[$object->field] = $object->field;
		}
		return $types;
	}
}
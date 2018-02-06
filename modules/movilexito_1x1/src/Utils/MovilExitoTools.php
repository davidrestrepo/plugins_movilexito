<?php 

namespace Drupal\movilexito_1x1\Utils;


class MovilExitoTools{

	public static function format_star($value, $maskingCharacter = '*'){
		return str_repeat($maskingCharacter, strlen($value) / 2) . substr($value,  strlen($value) / 2);
	}

	public static function  format_middle($value, $maskingCharacter = '*'){
		$response = str_repeat($maskingCharacter, strlen($value) / 3);
		if(strlen($value) % 2 == 1){
			$response .= substr($value, strlen($value) / 3, (strlen($value) / 3) + 1)
					  . str_repeat($maskingCharacter, (strlen($value) / 3) + 1) ;
		}else{
			$response .= substr($value, strlen($value) / 3, (strlen($value) / 3))
			. str_repeat($maskingCharacter, strlen($value) / 3) ;
		}

		return $response;		
	}

	public static function format_end($value, $maskingCharacter = '*'){
		$response;
		if(strlen($value) % 2 == 1){
			$response = substr($value, 0 ,strlen($value) / 2 + 1) ;
		}else{
			$response = substr($value, 0 ,strlen($value) / 2) ;
		}
		$response .= str_repeat($maskingCharacter, strlen($value) / 2);
		return $response;
	} 


	/**
	 * Validates that the field is no null.
	 */
	public static function validate_is_not_Null($value) {
	  $value = trim($value);
	  if(!empty($value)) {
	  	return TRUE;
	  }
	  return FALSE;
	}


	public static function validate_is_number($value){
		if(is_numeric($value)){
			return TRUE;
		}
		return FALSE;
	}

	public static function validate_chart_length($value, $lenght){
		if(strlen($value) == $lenght){
			return FALSE;
		}
		return TRUE;
	}

	public static function get_filter_number_phone($phone){
		return str_replace('+57', '', $phone);
	}
}

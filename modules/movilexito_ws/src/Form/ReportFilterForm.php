<?php

namespace Drupal\movilexito_ws\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormBase;
use Drupal\movilexito_ws\Report\Tools;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Defines a translation edit form.
 */
class ReportFilterForm extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'movilexito_ws_report_filter_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {
	  $form = array();

	  $form['filters'] = array(
	    '#type' => 'container',
	    '#theme' => 'fields_fieldset_like_table'
	    );

	  if( isset( $_SESSION['movilexito_ws']['report_filter']['from']['value'] ) ){
	    $today = date("Y-m-d",$_SESSION['movilexito_ws']['report_filter']['from']['value']);
	  }
	  else {
	    $today =  date("Y-m-d", strtotime("-14 day"));
	  }

	  $form['filters']['from'] = array(
	    '#title' => $this->t('Desde'),
	    '#type' => 'date',
	    '#default_value' => $today,
	    '#date_type' => DATE_DATETIME,
	    // '#date_timezone' => date_default_timezone(),
	    '#date_format' => 'd/m/Y',
	    '#date_increment' => 1,
	    '#date_year_range' => '-1:+3',
	    '#attributes' => array('placeholder' => $this->t('Start date')),
	    '#required' => TRUE,
	    );

	  $default_values = @$_SESSION['movilexito_ws']['report_filter'];

	  if( isset( $default_values['till']['value'] ) ){
	    $till = date("Y-m-d",$default_values['till']['value']);
	  }
	  else {
	    $till = date("Y-m-d");
	  }

	  $form['filters']['till'] = array(
	    '#title' => $this->t('Hasta'),
	    '#type' => 'date',
	    '#default_value' => $till,
	    '#date_type' => DATE_DATETIME,
	    // '#date_timezone' => date_default_timezone(),
	    '#date_format' => 'd/m/Y',
	    '#date_increment' => 1,
	    '#date_year_range' => '-1:+3',
	    '#size' => 10,
	    '#required' => TRUE,
	    );

	  $options = Tools::get_distinct_values("tipo_documento", true);
	  $form['filters']['tipo_documento'] = array(
	    '#type' => 'select',
	    '#title' => $this->t('Tipo documento'),
	    '#options' => $options,
	    '#default_value' => isset($default_values['tipo_documento']['value']) ? $default_values['tipo_documento']['value'] : "",
	    '#required' => FALSE,
	    );
	  unset($options);


	  $form['filters']['documento'] = array(
	    '#type' => 'textfield',
	    '#title' => $this->t('Documento'),
	    '#default_value' => isset($default_values['documento']['value']) ? $default_values['documento']['value'] : "",
	    '#size' => 30,
	    '#maxlength' => 128,
	    '#required' => FALSE,
	    );

	  $options = Tools::get_distinct_values("servicio_ws", true);
	  $form['filters']['servicio_ws'] = array(
	    '#type' => 'select',
	    '#title' => $this->t('Tipo documento'),
	    '#options' => $options,
	    '#default_value' => isset($default_values['servicio_ws']['value']) ? $default_values['servicio_ws']['value'] : "",
	    // '#required' => TRUE,
	  );
	  unset($options);

	  $form['filters']['action'] = array(
	    '#title' => 'Acciones',
	    '#type' => 'container',
	    );

	  $form['filters']['action']['submit'] = array(
	    '#type' => 'submit',
	    '#name' => 'filter',
	    '#value' => $this->t('Filtrar'),
	    );

	  $form['filters']['action']['download'] = array(
	    '#type' => 'submit',
	    '#name' => 'download',
	    '#value' => $this->t('Descargar'),
	    );

	  $form['filters']['action']['reset'] = array(
	    '#type' => 'submit',
	    '#name' => 'reset',
	    '#value' => $this->t('Reset'),
	    );

	  return $form;
	}
	

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {   
		  if(is_array($form_state->getValue('from')) || is_array($form_state->getValue('till'))){
		    return;
		  }
		  else {
		    $from = strtotime($form_state->getValue('from'));
		    $till = strtotime($form_state->getValue('till'));
		    $till_max = strtotime($form_state->getValue('from') ." +14 day");
		    if( $from > $till ){
		      $form_state->setErrorByName('from', t('La fecha inicial no puede ser mayor que la fecha final') );
		    }
		    if($till > $till_max ){
		      $form_state->setErrorByName('from', t('La fecha final no puede ser mayor a 15 días con respecto a la fecha inicial seleccionada. Máximo permitido de acuerdo a su selección: %max', ['%max' => date('d-m-Y', $till_max) ]));
		    }
		  } 
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$triggerdElement = $form_state->getTriggeringElement();
		$htmlIdofTriggeredElement = $triggerdElement['#name'];
		switch ($htmlIdofTriggeredElement) {
			case 'filter':
			case 'download':

		    $from = $form_state->getValue('from');
		    $till = $form_state->getValue('till');

		    $elements = $form_state->getValues();

		    if( isset($_SESSION['movilexito_ws']['report_filter']) ){
   	 			unset($_SESSION['movilexito_ws']['report_filter']);
  			}

				$_SESSION['movilexito_ws']['report_filter']['from'] = array(
					'field' => 'emsl.fecha_actualizacion',
					'value' => strtotime($elements['from'] . " 00:00:00"),
					'op' => '>=',
				);

				$_SESSION['movilexito_ws']['report_filter']['till'] = array(
					'field' => 'emsl.fecha_actualizacion',
					'value' => strtotime($elements['till'] . " 23:59:59"),
					'op' => '<=',
				);

				if(!empty($elements['tipo_documento'])){
					$_SESSION['movilexito_ws']['report_filter']['tipo_documento'] = array(
						'field' => 'emsl.tipo_documento',
						'value' => $elements['tipo_documento'],
						'op' => '=',
					);
				}

				if(!empty($elements['documento'])){
					$_SESSION['movilexito_ws']['report_filter']['documento'] = array(
						'field' => 'emsl.documento',
						'value' => $elements['documento'],
						'op' => '=',
					);
				}

				if(!empty( $elements['servicio_ws'])){
					$_SESSION['movilexito_ws']['report_filter']['servicio_ws'] = array(
						'field' => 'emsl.servicio_ws',
						'value' => $elements['servicio_ws'],
						'op' => '=',
					);
				}

				// print_r($_SESSION['movilexito_ws']['report_filter']);
				// die;

				if($htmlIdofTriggeredElement == 'download'){
					$response = new RedirectResponse('download_report');
			  	$response->send();
			  	return;
				}
				break;

			case 'reset':
				unset($_SESSION['movilexito_ws']['report_filter']);
			break;
		}
	}
}
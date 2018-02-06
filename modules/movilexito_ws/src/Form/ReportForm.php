<?php

namespace Drupal\movilexito_ws\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormBase;
use Drupal\movilexito_ws\Report\Tools;

/**
 * Defines a translation edit form.
 */
class ReportForm extends FormBase {

	  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movilexito_ws_report_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

  	$filter = Tools::filter_query();

	 $form['movilexito_ws_filter_form'] = \Drupal::formBuilder()->getForm('Drupal\movilexito_ws\Form\ReportFilterForm');
	 $header = array(
    	array('data' => $this->t('Tipo documento'), 'field' => 'tipo_documento', 'sort' => 'asc'),
    	array('data' => $this->t('Documento'), 'field' => 'documento', 'sort' => 'asc'),
    	array('data' => $this->t('IP'), 'field' => 'user_ip', 'sort' => 'asc'),
    	array('data' => $this->t('Resultado WS'), 'field' => 'resultado_ws', 'sort' => 'asc'),
    	array('data' => $this->t('Detalle'), 'field' => 'resultado_ws_detalle', 'sort' => 'asc'),
    	array('data' => $this->t('Servicio WS'), 'field' => 'servicio_ws', 'sort' => 'asc'),
    	array('data' => $this->t('Fecha de actualización'), 'field' => 'fecha_actualizacion', 'sort' => 'asc'),
    );

   // print_r($filter);
   // die;
	 
    $db = \Drupal::database();
    $query = $db->select('exito_me_ws_log','emsl');
    $query->fields('emsl');

    // print_r($filter);
    // die;
    if(!empty($filter['where'])) {
    	$query->where($filter['where'], $filter['args']);
  	}


    // The actual action of sorting the rows is here.
    $table_sort = $query->extend('Drupal\Core\Database\Query\TableSortExtender')
                        ->orderByHeader($header);
    // Limit the rows to 20 for each page.
    $pager = $table_sort->extend('Drupal\Core\Database\Query\PagerSelectExtender')
                        ->limit(20);
    $result = $pager->execute();
 
    // Populate the rows.
    $rows = array();
    foreach($result as $row) {
      $rows[] = array('data' => array(
        'tipo_documento' => $row->tipo_documento,
        'documento' => $row->documento,
        'user_ip' => $row->user_ip,
        'resultado_ws' => $row->resultado_ws,
        'resultado_ws_detalle' => $row->resultado_ws_detalle,
        'servicio_ws' => $row->servicio_ws,
        'fecha_actualizacion' => $row->fecha_actualizacion,
      ));
    }
 
    // Generate the table.
    $form['report_table'] = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No se encontraron resultados con los parametros de busqueda.'),
    );
 
    // Finally add the pager.
    $form['pager'] = array(
      '#type' => 'pager'
    );
 
    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}
}
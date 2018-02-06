<?php

namespace Drupal\movilexito_ws\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\movilexito_ws\Report\Tools;


/**
 * obtiene el reporte de las transacciones de cambios de línea
 */
class MovilExitoWSReportController extends ControllerBase {


	public function report(){
		return [
			'form' => $this->formBuilder()->getForm('Drupal\movilexito_ws\Form\ReportForm'),
		];
	} 

	public function download(){
		$filter = Tools::filter_query();
		$header = array(
			array('data' => $this->t('Tipo documento'), 'field' => 'tipo_documento', 'sort' => 'asc'),
			array('data' => $this->t('Documento'), 'field' => 'documento', 'sort' => 'asc'),
			array('data' => $this->t('IP'), 'field' => 'user_ip', 'sort' => 'asc'),
			array('data' => $this->t('Resultado WS'), 'field' => 'resultado_ws', 'sort' => 'asc'),
			array('data' => $this->t('Detalle'), 'field' => 'resultado_ws_detalle', 'sort' => 'asc'),
			array('data' => $this->t('Servicio WS'), 'field' => 'servicio_ws', 'sort' => 'asc'),
			array('data' => $this->t('Fecha de actualización'), 'field' => 'fecha_actualizacion', 'sort' => 'asc')
		);
		$db = \Drupal::database();
    $query = $db->select('exito_me_ws_log','emsl');
    $query->fields('emsl', [
    	'tipo_documento', 
    	'documento', 
    	'user_ip', 
    	'resultado_ws', 
    	'resultado_ws_detalle',
    	'servicio_ws',
    	'fecha_actualizacion'
    ]);

    if(!empty($filter['where'])) {
    	$query->where($filter['where'], $filter['args']);
  	}
  	    // The actual action of sorting the rows is here.
    // The actual action of sorting the rows is here.
    $table_sort = $query->extend('Drupal\Core\Database\Query\TableSortExtender')
                        ->orderByHeader($header);
    // Limit the rows to 20 for each page.
    $pager = $table_sort->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $result = $pager->execute();


    $rows_query = array();
		foreach($result as $row){
			$rows_query[] = (array) $row;
		}

		//Send response headers to the browser
		// drupal_add_http_header('Content-Type', 'text/csv');
		// drupal_add_http_header('Content-Disposition', 'attachment;filename=reporte_transacciones.csv');
		
		header("Content-type: text/csv");
  	header("Content-disposition: attachment; filename = report.csv");


		$header_csv = array();
		foreach ($header as $row) {
		  $header_csv[] = $row['data'];
		}

		$fp = fopen('php://output', 'w');
		fputcsv($fp, $header_csv);
		foreach ($rows_query as $line) {
		  fputcsv($fp, $line);
		}
		fclose($fp);
		print_r($fp);
		die;
	}

}
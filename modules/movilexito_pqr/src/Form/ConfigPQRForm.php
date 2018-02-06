<?php  

namespace Drupal\movilexito_pqr\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormBase;


/**
* Defines a translation edit form.
*/
class ConfigPQRForm extends FormBase 
{

	public function getFormId()
	{
		return 'movilexito_pqr_config_form';
	}


  /**
   * Construye el formualrio de configuración de PQR.
   *
   * @return array $form
   */
	public function buildForm(array $form, FormStateInterface $form_state)
	{
		$form = array();

		$form['pqr_form'] = array(
			'#type' => 'vertical_tabs',
		    '#prefix' => '<h2><small>' . t('Configuración de parametros para PQR') . '</small></h2>',
		    '#weight' => -10,
		    );

		     
		$form['config_pqr'] = array(
		    '#type' => 'fieldset',
		    '#group' => 'pqr_form',
		    '#tree' => TRUE,
		    '#title' => 'Configurar parametros PQR',
		    );

	    $form['config_pqr']['pqr_emails'] = array(
		    '#type' => 'textarea',
		    '#title' => 'Correos electrónicos para envío de PQR',
		    '#description' => 'Correos donde se debe enviar la información obtenida en el formulario PQR. Por favor separar por coma (,)',
		    '#default_value' => \Drupal::state()->get('pqr_emails'),
		    '#required' => TRUE,
		    );

	    $form['config_pqr']['pqr_extensions'] = array(
		    '#type' => 'textfield',
		    '#title' => 'Extensiones de archivos',
		    '#description' => 'Extensiones permitidas en adjuntos del PQR. Por favor separar por coma (,) no dejar espacios, no anteceder con punto (.). Ejm: zip,doc,xlsx',
		    '#default_value' => \Drupal::state()->get('pqr_extensions'),
		    '#required' => TRUE,
		    );

	    $form['config_pqr']['pqr_size'] = array(
		    '#type' => 'textfield',
		    '#title' => 'Tamaño máximo por archivo',
		    '#description' => 'Tamaño del archivo adjunto. Solo ingresar MB o KB, el máximo por defecto es de 25MB. Ejm: "10 MB" o "200 KB"',
		    '#default_value' => \Drupal::state()->get('pqr_size'),
		    '#required' => TRUE,
		    );

	    $form['config_pqr']['pqr_delete_emails'] = array(
		    '#type' => 'select',
		    '#title' => 'Eliminar correos represedados',
		    '#description' => 'Seleccione el tiempo de eliminación de regitros, luego de su creación',
		    '#default_value' => \Drupal::state()->get('pqr_delete_emails'),
		    '#options' => [
                '0' => 'Nunca',
                '3600' => '1 hora',
                '10800' => '3 horas',
                '21600' => '6 horas',
                '43200' => '12 horas',
                '86400' => '1 día',
                '172800' => '2 días',
                '604800' => '1 semana',
            ],
		    '#required' => TRUE,
		    );

	    $form['config_pqr']['submit'] = array(
	        '#title' => 'Guardar',
	        '#value' => 'Guardar',
	        '#type' => 'submit',
	    );

	    return $form;
	}
	
	 /**
   * Valida campos de form.
   * @param $form , $form state
   * @return array $form
   */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      
        $data = $form_state->getValues();


        //pqr_emails
        if (!empty($data['config_pqr']['pqr_emails'])) {
        	$emails = explode(',', $data['config_pqr']['pqr_emails']);
        	$val = TRUE;
        	foreach ($emails as $value) {
        		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				    $val = FALSE;
				}
        	}

        	if (!$val) {
        		$form_state->setErrorByName('pqr_emails', t('Verifique los correos electrónicos para envío del PQR, no son validos.'));
        	}
        }

        //pqr_extensions
        if (!empty($data['config_pqr']['pqr_extensions'])) {
        	$extns = explode(',', $data['config_pqr']['pqr_extensions']);
        	$val = TRUE;
        	foreach ($extns as $value) {
        		if (preg_match('/[^a-z]/',$value) || empty($value)) {
				    $val = FALSE;
				}
        	}

        	if (!$val) {
        		$form_state->setErrorByName('pqr_extensions', t('Verifique extensiones de archivos, no son validas.'));
        	}
        }

        //pqr_size
        if (!empty($data['config_pqr']['pqr_size'])) {
        	$size = explode(' ', $data['config_pqr']['pqr_size']);
        	$val = TRUE;
        	if (count($size) !== 2 || !is_numeric($size[0]) || !in_array($size[1], array('MB', 'KB'))) {
        		$form_state->setErrorByName('pqr_size', t('Verifique el tamaño por archivo, no es válido.'));
        	}elseif ($size[1] == 'MB' &&  is_numeric($size[0]) && $size[0] > 25) {
        		$form_state->setErrorByName('pqr_size', t('El tamaño máximo es de 25 MB.'));
        	}
        	
        }
    }

    /**
	 * {@inheritdoc}
    */
	public function submitForm(array &$form, FormStateInterface $form_state) 
	{
		$data = $form_state->getValues();
	    \Drupal::state()->setMultiple($data['config_pqr']);
	    drupal_set_message($this->t('The strings have been saved.'));    
	}
}

?>
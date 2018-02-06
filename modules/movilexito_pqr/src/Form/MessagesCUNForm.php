<?php

namespace Drupal\movilexito_pqr\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormBase;


/**
 * Defines a translation edit form.
 */
class MessagesCUNForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movilexito_pqr_message_cun_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();

  $form['cun_form'] = array(
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Configuración de mensaje de respuesta por fallo en consulta a CUN') . '</small></h2>',
    '#weight' => -10,
    );

     
  $form['cun_message'] = array(
    '#type' => 'fieldset',
    '#group' => 'cun_form',
    '#tree' => TRUE,
    '#title' => 'Configurar mensaje CUN',
    );
  $form['cun_message']['error_cun_message'] = array(
    '#type' => 'textarea',
    '#title' => 'Mensaje para fallo de consulta al CUN',
    '#default_value' => \Drupal::state()->get('error_cun_message'),
    '#required' => TRUE,
    );

  $form['cun_message']['submit'] = array(
        '#title' => 'Guardar',
        '#value' => 'Guardar',
        '#type' => 'submit',
        //'#submit' => array(''),
    );
  ###################################################################

 
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
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $data = $form_state->getValues();

    \Drupal::state()->setMultiple($data['cun_message']);
    
    drupal_set_message($this->t('The strings have been saved.'));    
  }

}

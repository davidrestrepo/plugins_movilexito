<?php

namespace Drupal\movilexito_ws\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormBase;


/**
 * Defines a translation edit form.
 */
class messagesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movilexito_ws_messages_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();

  $form['ws_form'] = array(
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Configuración de mensajes de respuesta de movilexito') . '</small></h2>',
    '#weight' => -10,
    );

     
  $form['1x1_ws_messages'] = array(
    '#type' => 'fieldset',
    '#group' => 'ws_form',
    '#tree' => TRUE,
    '#title' => 'Servicios movilexito 1x1',
    '#description' => 'Configuración de datos de servicio de solicitud de token',
    );
  $form['1x1_ws_messages']['validateform_message'] = array(
    '#type' => 'textarea',
    '#title' => 'Mensaje Intro modal de validación',
    '#default_value' => \Drupal::state()->get('validateform_message'),
    '#description' => "Mensaje explicativo a mostrar en el modal de validación",
    '#required' => TRUE,
    );
  $form['1x1_ws_messages']['validatefields_message'] = array(
    '#type' => 'textarea',
    '#title' => 'Mensaje campos requeridos modal de validación',
    '#default_value' => \Drupal::state()->get('validatefields_message'),
    '#description' => "Mensaje de vaidación de campos de modal",
    '#required' => TRUE,
    );
  $form['1x1_ws_messages']['error_message'] = array(
    '#type' => 'textarea',
    '#title' => 'Error de webservice',
    '#default_value' => \Drupal::state()->get('error_message'),
    '#description' => "Error de webservice",
    '#required' => TRUE,
    );
  $form['1x1_ws_messages']['habeasdata_message'] = array(
    '#type' => 'textarea',
    '#title' => 'Habeas data',
    '#default_value' => \Drupal::state()->get('habeasdata_message'),
    '#description' => "Mensaje a mostrar cuando no hay habeas data.",
    '#required' => TRUE,
    );  
  $form['1x1_ws_messages']['nouser_message'] = array(
    '#type' => 'textarea',
    '#title' => 'No usuario',
    '#default_value' => \Drupal::state()->get('nouser_message'),
    '#description' => "Mensaje cuando no se encuentra el usuario",
    '#required' => TRUE,
    );
  $form['1x1_ws_messages']['linea_no_activa'] = array(
    '#type' => 'textarea',
    '#title' => 'Linea no activa',
    '#default_value' => \Drupal::state()->get('linea_no_activa'),
    '#description' => "Mensaje cuando la linea no se encuentra activa",
    '#required' => TRUE,
    );
   $form['1x1_ws_messages']['validacion_erronea'] = array(
    '#type' => 'textarea',
    '#title' => 'Validación de datos erronea',
    '#default_value' => \Drupal::state()->get('validacion_erronea'),
    '#description' => "Mensaje de validación de datos personales erroneos",
    '#required' => TRUE,
    );

  $form['1x1_ws_messages']['submit'] = array(
        '#title' => 'Aceptar',
        '#value' => 'Aceptar',
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

    $dataWs = $form_state->getValues();

    \Drupal::state()->setMultiple($dataWs['1x1_ws_messages']);
    
    drupal_set_message($this->t('The strings have been saved.'));    
  }

}

<?php

namespace Drupal\movilexito_ws\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormBase;


/**
 * Defines a translation edit form.
 */
class registerForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movilexito_ws_register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();

  $form['ws_form'] = array(
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Configuración de datos ws movilexito') . '</small></h2>',
    '#weight' => -10,
    );

     
  $form['1x1_ws_settings'] = array(
    '#type' => 'fieldset',
    '#group' => 'ws_form',
    '#tree' => TRUE,
    '#title' => 'Servicios movilexito 1x1',
    '#description' => 'Configuración de datos de servicio de solicitud de token',
    );
  $form['1x1_ws_settings']['token_ws_endpoint'] = array(
    '#type' => 'textfield',
    '#title' => 'Endpoint token',
    '#default_value' => \Drupal::state()->get('token_ws_endpoint'),
    '#description' => "Url endpoint del servicio.",
    '#required' => TRUE,
    );
  $form['1x1_ws_settings']['token_ws_user'] = array(
    '#type' => 'textfield',
    '#title' => 'User token',
    '#default_value' => \Drupal::state()->get('token_ws_user'),
    '#description' => "Usuario",
    '#required' => TRUE,
    );
  $form['1x1_ws_settings']['token_ws_pass'] = array(
    '#type' => 'textfield',
    '#title' => 'Password token',
    '#default_value' => \Drupal::state()->get('token_ws_pass'),
    '#description' => "Contraseña",
    '#required' => TRUE,
    );
  $form['1x1_ws_settings']['queryuser_ws_endpoint'] = array(
    '#type' => 'textfield',
    '#title' => 'Endpoint consulta de usuario',
    '#default_value' => \Drupal::state()->get('queryuser_ws_endpoint'),
    '#description' => "Endpoint ws consulta de usuario",
    '#required' => TRUE,
    );

  $form['1x1_ws_settings']['queryvalidatenumber_ws_endpoint'] = array(
    '#type' => 'textfield',
    '#title' => 'Endpoint validar numero',
    '#default_value' => \Drupal::state()->get('queryvalidatenumber_ws_endpoint'),
    '#description' => "Endpoint ws validación número",
    '#required' => TRUE,
    );

  $form['1x1_ws_settings']['saveline_ws_endpoint'] = array(
    '#type' => 'textfield',
    '#title' => 'Endpoint guardar número favorito',
    '#default_value' => \Drupal::state()->get('saveline_ws_endpoint'),
    '#description' => "Endpoint ws guardar número",
    '#required' => TRUE,
    );


  $form['1x1_ws_settings']['submit'] = array(
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

    \Drupal::state()->setMultiple($dataWs['1x1_ws_settings']);
    
    drupal_set_message($this->t('The strings have been saved.'));    
  }

}

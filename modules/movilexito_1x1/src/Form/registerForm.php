<?php
namespace Drupal\movilexito_1x1\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\movilexito_ws\Controller\MovilexitoWsController;
use Drupal\movilexito_1x1\Controller\Movilexito1x1Controller;
use Drupal\movilexito_1x1\Utils\MovilExitoTools;
use Drupal\movilexito_1x1\Form\ModalForm;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use GuzzleHttp\Exception\RequestException;


/**
 * Defines a translation edit form.
 */
class RegisterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movilexito_1x1_register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['user_data'] = array(
        '#type' => 'fieldset',
        '#description' => '',
        '#title' => $this->t('Línea favorita'),
        // '#tree' => TRUE,
    );

    $arrayTipoDoc = [
      1 => 'Cédula', 
      3 => 'Cédula de extranjería', 
      4 => 'Pasaporte'
    ];

    $form['user_data']['tipo_documento'] = array(
       '#title' => $this->t('Tipo de Identificación'),
       '#title_display' => "invisible",
       '#type' => 'select',
       '#empty_option' => $this->t('Tipo de identificación*:'),
       '#attributes' => array('placeholder' => $this->t('Tipo de identificación*:')),
       '#options' =>  $arrayTipoDoc,
       '#description' => $this->t('Tipo de documento'),
       '#default_value' => isset($userData['tipo_documento']) ? $userData['tipo_documento'] : 'Cédula', 
   );

    $form['user_data']['documento'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Documento'),
        '#description' => $this->t('Documento'),
        '#attributes' => array('placeholder' => 'Documento*:'),
        '#maxlength' => 15,
    );

    $form['user_data']['linea'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Nueva linea favorita'),
        '#description' => $this->t('Nueva linea favorita'),
        '#maxlength' => 10,
        '#attributes' => array(
          'placeholder' => $this->t('Nueva línea favorita*:'),
        ),
    );
    $form['user_data']['actions'] = array('#type' => 'actions');
    $form['user_data']['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Continuar'),       
        '#ajax' => [
          'callback' => 'Drupal\movilexito_1x1\Form\RegisterForm::submit_form_callback',
          'event' => 'click',
        ]
    );

    $form['#attached']['drupalSettings']['errorMsg'] = \Drupal::state()->get('error_message');
    $form['#attached']['library'][] = 'movilexito_1x1/movilexito_1x1';

    return $form;
  }

  /**
  * Callback for opening the modal form.
  */
  public static function submit_form_callback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $error = FALSE;
    $html_error = '';
   
    // se validad el tipo de documento
    if(!MovilExitoTools::validate_is_not_Null($form_state->getValue('tipo_documento'))){
      $html_error .= '<li>'. t('El tipo de documento es requerido.').'</li>';
      $error = TRUE;
    }

    // se validad el número de cédula
    if(MovilExitoTools::validate_is_not_Null($form_state->getValue('documento'))){
      if(!MovilExitoTools::validate_is_number($form_state->getValue('documento'))){
        $html_error .= '<li>'. t('El número de documento debe ser númerico.').'</li>';
        $error = TRUE;
      }
    }else{
      $html_error .= '<li>'. t('El número de documento es requerido.').'</li>';
      $error = TRUE;
    }

    // se validad el número de línea
    if(MovilExitoTools::validate_is_not_Null($form_state->getValue('linea'))){
      if(!MovilExitoTools::validate_is_number($form_state->getValue('linea'))){
        $html_error .= '<li>'. t('El número de línea debe ser númerico.').'</li>';
        $error = TRUE;
      }

      if(MovilExitoTools::validate_chart_length($form_state->getValue('linea'), 10)){
        $html_error .= '<li>'. t('El número de línea debe tener 10 dígitos.').'</li>';
        $error = TRUE;
      }
    }else{
      $html_error .= '<li>'. t('El número de línea es requerido').'</li>';
      $error = TRUE;
    }

      // $html_error .= '<li><pre>'.print_r($form_state->getUserInput(),1).'</pre></li>';


    if($error){
      $html_error = '<div role="contentinfo" aria-label="Mensaje de error" class="messages messages--error"><div role="alert"><ul>' . $html_error . '</ul></div></div>';
      $response->addCommand(new CssCommand(
        '.message-info',
        [ 'display' => 'inline-block', /*'background' => '#ff5050'*/ ]
      ));
      $response->addCommand(new HtmlCommand(
        '.message-info',
        $html_error . '<script type="text/javascript">
        Drupal.behaviors.scroll_top();
        </script>'
      ));
      return $response;
    }
   
    $options = [
      'width' => '500',
    ];
 
     
      $ws = new MovilexitoWsController;         
      $habeas_info =  $ws->consumeGetUser($form_state->getValue('tipo_documento'),$form_state->getValue('documento'));
      

      //SET INFORMACIÓN DE MUESTRA PARA QUE EL USUARIO VALIDE
      if(isset($habeas_info->OCSHabeasData) && $habeas_info->OCSHabeasData == 'Y'){ 

        $dateB = explode(' ',$habeas_info->birthDate);
        $birthDate = $dateB[0];
        $telephone = MovilExitoTools::get_filter_number_phone($habeas_info->cellularPhone);
     
        $_SESSION['movilexito_1x1']['data_user'] = [
              'email' => $habeas_info->emailAddress,
              'birthdate' => $birthDate,
              'telephone' => $telephone,
              'tipo_documento' => $form_state->getValue('tipo_documento'),
              'documento' => $form_state->getValue('documento'),
              'linea' => $form_state->getValue('linea'),
            ];

            $form_modal_html = '
            <!-- Modal -->                        
            <div id="modalerror" style="display:none;" role="contentinfo" aria-label="Mensaje de error" class="messages messages--error"><div id="ulError" role="alert"></div></div>
            <div id="modalLoader" style="display:none"><img src="/core/themes/stable/images/core/throbber-active.gif"> Espere, por favor...</div>
              </div>
              <div id="modalExito" class="content-modal-form_exito js-form-wrapper form-wrapper" data-drupal-selector="edit-content" aria-describedby="edit-content--Ww4tjCUTbDU--description" id="edit-content--Ww4tjCUTbDU">
              <div>'.\Drupal::state()->get('validateform_message').'</div>
              <div class="js-form-item form-item js-form-type-textfield form-type-textfield js-form-item-email form-item-email">
                <label for="edit-email--OweHBBGkR0g">Correo Eletrónico</label>
                  <input placeholder="'.MovilExitoTools::format_middle($habeas_info->emailAddress).'" name="email" id="email" value="" size="60" maxlength="128" class="form-text">
              </div>
              <div class="js-form-item form-item js-form-type-textfield form-type-textfield js-form-item-birthdate form-item-birthdate">
                <label for="edit-birthdate--gzWhinj-zmk">Fecha de Nacimiento</label>
                <input placeholder="dd/mm/aaaa" name="birthdate" id="birthdate" value="" size="60" maxlength="128" class="form-text">
              </div>
              <div class="js-form-item form-item js-form-type-textfield form-type-textfield js-form-item-telephone form-item-telephone">
                <label for="edit-telephone--tifYJY5pgSk">Teléfono</label>
                <input placeholder="'.MovilExitoTools::format_end($telephone).'" name="telephone" id="telephone" value="" size="60" maxlength="128" class="form-text">
              </div>
              <div class="form-actions js-form-wrapper form-wrapper">
                <input type="button" id="submit_modal_form" name="submit_modal_form" value="Continuar" class="button form-submit" onclick="Drupal.behaviors.submit_form_modal()">
              </div>
              ';
             $response->addCommand(new OpenModalDialogCommand(
              'Validación Usuario', 
              $form_modal_html,
              $options
            ));
      }elseif(isset($habeas_info->OCSHabeasData) && $habeas_info->OCSHabeasData == 'N'){
        //array con datos para almacenar en log
        
        $wsLogData['tipo_documento'] = $form_state->getValue('tipo_documento');
        $wsLogData['documento'] = $form_state->getValue('documento');        
        $wsLogData['servicio_ws'] = 'ClienteUnico/Cliente';
        $message = \Drupal::state()->get('habeasdata_message');
        $wsLogData['resultado_ws'] = 'No habeas data';
        $wsLogData['resultado_ws_detalle'] = 'El usuario no autoriza habeas data';
        $ws->saveWsLog($wsLogData);

        $response->addCommand(new OpenModalDialogCommand(
          "Estado del proceso",
          $message,
          $options
        ));
      }elseif (isset($habeas_info->OCSHabeasData) && empty($habeas_info->OCSHabeasData)) {
        $wsLogData['tipo_documento'] = $form_state->getValue('tipo_documento');
        $wsLogData['documento'] = $form_state->getValue('documento');        
        $wsLogData['servicio_ws'] = 'ClienteUnico/Cliente';
        $wsLogData['resultado_ws'] = 'vacio';
        $wsLogData['resultado_ws_detalle'] = 'No se encuentra usuario en sistema';
        $ws->saveWsLog($wsLogData);
        $message = \Drupal::state()->get('nouser_message');        

        $response->addCommand(new OpenModalDialogCommand(
          "Estado del proceso",
          $message,
          $options
        ));
      }
      else{
         $response->addCommand(new OpenModalDialogCommand(
          "Estado del proceso",
          $habeas_info->errorMsg,
          $options
        ));
      }
    
    return $response;
  }
   /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $result = new ReplaceCommand('#message-error', 'error');
    $response->addCommand($result);

    return $response;
  }

  public function ajax_submit_form_callback(array &$form, FormStateInterface $form_state) {
    // drupal_set_message(t('HOMAR'), 'status');
    $AjaxResponse = new AjaxResponse();
    $response->addCommand(new ReplaceCommand(
      '#message-error',
      'error'
    ));
    return $response;
  }


}

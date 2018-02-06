<?php

namespace Drupal\movilexito_pqr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * obtiene datos y valida registro movilexito 1x1.
 */
class MovilExitoPQRController extends ControllerBase {
  
  /**
   * retorna formulario de consulta pqr.
   *
   * @return object form
   */
  public function consultPQR() {
    $formName = 'Drupal\movilexito_pqr\Form\ConsultPQRForm';
    $form = \Drupal::formBuilder()->getForm($formName);
    return [
      '#theme' => 'consult-pqr',
      '#form' => $form
    ];
  }

  /**
   * retorna formulario iframe pqr.
   *
   * @return object form
   */
  public function consultPQRIframe() {
    $formName = 'Drupal\movilexito_pqr\Form\ConsultPQRForm';
    $form = \Drupal::formBuilder()->getForm($formName);
    return [
      '#theme' => 'consult-pqr-frame',
      '#form' => $form
    ];
  }

  /**
   * retorna formulario de nuevo pqr.
   *
   * @return object form
   */
  public function addPQR() {
    $formName = 'Drupal\movilexito_pqr\Form\AddPQRForm';
    $form = \Drupal::formBuilder()->getForm($formName);
    return [
      'form' => $form
    ];
  }

  /**
   * retorna formulario de mensajes cun.
   *
   * @return object form
   */
  public function messagesCUN() {
    $formName = 'Drupal\movilexito_pqr\Form\MessagesCUNForm';
    $form = \Drupal::formBuilder()->getForm($formName);
    return [
      'form' => $form
    ];
  }

  /**
   * retorna formulario configuracion.
   *
   * @return object form
  */
  public function configPQR() {
    $formName = 'Drupal\movilexito_pqr\Form\ConfigPQRForm';
    $form = \Drupal::formBuilder()->getForm($formName);
    return [
      'form' => $form
    ];
  }
  
  public function reportPQR() {
    $query = db_select('movilexito_crn_email', 'e');
    $query->fields('e');
    $response = $query->execute()->fetchAll();
    return [
      '#theme' => 'report-pqr',
      '#test_var' => $response,

    ];
  }


  /**
   * envía mensaje email de actualización pqr.
   * @param array $data datos de usuario para email
   * @return object form
   */
    public function enviar_pqr($data){

        $emails = \Drupal::state()->get('pqr_emails');
        // tied to a user account, use the site's default language.
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        //  LanguageManagerInterface::getDefaultLanguage()->getId();

        $params = $data;
         

        // Send the e-mail to the asker. Drupal calls hook_mail() via this.
        $mail_sent = \Drupal::service('plugin.manager.mail')->mail('movilexito_pqr', 'envio_pqr', $emails, $langcode, $params, NULL, TRUE);
         
        // Handle sending result.
        if ($mail_sent) {
            return true;
        }else {
            return false;
        }
    }
  
}



<?php

namespace Drupal\movilexito_1x1\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\movilexito_ws\Controller\MovilexitoWsController;

/**
 * obtiene datos y valida registro movilexito 1x1.
 */
class Movilexito1x1Controller extends ControllerBase {

    /**
     * Symfony\Component\HttpFoundation\RequestStack definition.
     *
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;


    /**
     * Constructs a new PaymentController object.
     */
    public function __construct(RequestStack $request_stack)
    {
        $this->requestStack = $request_stack;
    }

    /**
     *
     * create a new stack
     */
    public static function create(ContainerInterface $container)
    {
        return new static($container->get('request_stack'));
    }

  /**
   * Shows the string search screen.
   *
   * @return array
   *   The render array for the string search screen.
   */
  public function registerPage() {

    $form = $this->formBuilder()->getForm('Drupal\movilexito_1x1\Form\registerForm');
    return [
      '#theme' => 'register-line',
      '#form' => $form
    ];    
  }

  /**
   * Realiza validaciones de formulario modal de validación de usuario.
   *
   * @return array $response
   *   Respuesta de los servicios consumidos.
   */
  public function submitRegister(){
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');
    $data_old = $_SESSION['movilexito_1x1']['data_user'];
    $data = [
        'status' => 'success',
        'message' => '',
        'data' => NULL
    ];
    $request = $this->requestStack->getCurrentRequest();
    if ($request->getMethod() === 'POST') {
      $email = $request->request->get('email');
      $telephone = $request->request->get('telephone');
      $birthdate = $request->request->get('birthdate');
      $data['data'] = [
        'email' => $email,
        'telephone' => $telephone,
        'birthdate' => $birthdate
      ];
      //REALIZAR LAS VALIDACIONES PERTINENTES EN ESTE ESPACIO
      if(empty($email) && empty($birthdate) && empty($telephone)){
        $data['message'] = \Drupal::state()->get('validatefields_message');
      }else{
        if((!empty($email) && ($email !== $data_old['email'])) || 
          (!empty($birthdate) && ($birthdate !== $data_old['birthdate'])) || 
          (!empty($telephone) && ($telephone !== $data_old['telephone']))){
          $data['message'] = \Drupal::state()->get('validacion_erronea');
        }

        if($data['message'] == ''){
          //Valida que el numero a registrar este activo
          $valNumber = $this->getValidateNumber($data_old['tipo_documento'], $data_old['documento'], $data_old['linea']);

          if(!empty($valNumber)){
            $data['message'] = $valNumber;
          }else{
            $updateNumber = $this->updateNumber($data_old['tipo_documento'], $data_old['documento'], $data_old['linea']);
            $data['message'] = $updateNumber;            
          }  
        }         
        
      }     

    }else{
      $data['message'] = $this->t('Method not allowed');
    }

    //RESPUESTA
    $response->setContent(json_encode($data));
    return $response;
  }


  /**
  *Valida si linea a ingresar a favoritos esta activa
  * @param string $documentType = tipo de documento
  * @param string $document = documento
  * @param string $number = linea a validar
  * @return string $responseMsg = Mensaje de respuesta de servicio
  */  
  static public function getValidateNumber($documentType,$document,$number){
    $ws = new MovilexitoWsController;   
      
    $responseValNumber =  $ws->consumeValidateUserNumber($number,$documentType,$document);
    $responseMsg = '';   

   if(isset($responseValNumber->statusId) && $responseValNumber->statusId != 1 ){
      $responseMsg = \Drupal::state()->get('linea_no_activa');
      //array con datos para almacenar en log
      $wsLogData = array('tipo_documento' => $documentType,'documento' => $document);
      $wsLogData['servicio_ws'] = 'APP/MovilExito/V1/Consulta?msisdn';
      $wsLogData['resultado_ws'] = $responseValNumber->status;
      $wsLogData['resultado_ws_detalle'] = $responseValNumber->ErrorRest->errorDesc;
      $ws->saveWsLog($wsLogData);        
    }

    if(isset($responseValNumber->errorMsg)){
      $responseMsg = $responseValNumber->errorMsg;
    }

    return $responseMsg;    
  }

  /**
  *Actualiza numero activo
  * @param string $documentType = tipo de documento
  * @param string $document = documento
  * @param string $number = linea a validar
  * @return string $responseMsg = Mensaje de respuesta de servicio
  */
  static public function updateNumber($documentType,$document,$number){
    $ws = new MovilexitoWsController;

    //valida tipo de documento para servicio de update cedula = 2
    if($documentType == 1){
      $documentType = 2;
    }
      
    $responseUpdate =  $ws->consumeSaveNumber($number,$documentType,$document);
    $responseMsg = '';   

   if(isset($responseUpdate->ErrorRest->errorDesc)){
      $responseMsg = $responseUpdate->ErrorRest->errorDesc;
      //array con datos para almacenar en log
      $wsLogData = array('tipo_documento' => $documentType,'documento' => $document);
      $wsLogData['servicio_ws'] = '/LineaFavorita/v1';
      $wsLogData['resultado_ws'] = $responseUpdate->ErrorRest->errorCode;
      $wsLogData['resultado_ws_detalle'] = $responseUpdate->ErrorRest->errorDesc;
      $ws->saveWsLog($wsLogData);        
    }

    if(isset($responseValNumber->errorMsg)){
      $responseMsg = $responseUpdate->errorMsg;
    }

    return $responseMsg;    
  }
  
}



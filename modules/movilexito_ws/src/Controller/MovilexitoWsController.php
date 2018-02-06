<?php

namespace Drupal\movilexito_ws\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation;
use GuzzleHttp\Exception\RequestException;

/**
 * obtiene datos y valida registro movilexito 1x1.
 */
class MovilexitoWsController extends ControllerBase {


  /**
   * muestra formulario de registro de datos ws.
   *   
   */
  public function wsregisterPage() {
    return [      
      'form' => $this->formBuilder()->getForm('Drupal\movilexito_ws\Form\registerForm'),
    ];
  }

  /**
   * muestra formulario de registro de mensajes de error ws.
   *   
   */
  public function wsmessagesPage() {
    return [      
      'form' => $this->formBuilder()->getForm('Drupal\movilexito_ws\Form\messagesForm'),
    ];
  }


  /**
   * consume servicio ws pasado por parametro.
   * @param string $requesName: Nombre de servicio a solicitar
   * @param string $method: metdo de servicio
   * @param string $url: endpoint del servicio
   * @param string $wsOptions: opciones adicionales de consumo    
   * @return array $body: respuesta del servicio consumido
   */
  public function consumeService($requestName,$method,$url,$wsOptions){
    $client = \Drupal::httpClient();
   
    $options = [
      'verify' => false,            
     'headers' => [
        'Content-Type' => 'application/json','Accept' => 'application/json',        
      ],
    ];

    $options['http_errors'] = FALSE;

    //Completa las opncoines básicas con las pasadas por parametro
    if(!empty($wsOptions)){
      foreach ($wsOptions as $key => $value) {
        $options[$key] = $value;
      }      
    }
   
    try {
      
      
      $response = $client->request($method, $url, $options);
      $code = $response->getStatusCode();
      $body = "";

      if ($code == 200) {
        $body = json_decode($response->getBody()->getContents());
        if(isset($body->errorCode) && $body->errorCode == 'OSB-380000'){
          $response->errorMsg = \Drupal::state()->get('error_message');      
          return $response;
        }
        else{
          return $body;  
        }                     
      }
      else{
        \Drupal::logger('movilexito_1x1')->error(json_encode($response));
         $body->errorMsg = \Drupal::state()->get('error_message');
         return $body;
      }
    }
    catch (RequestException $e) {
      watchdog_exception($requestName, $e); 
      $responseMsg = new \stdClass;     
      $responseMsg->errorMsg = \Drupal::state()->get('error_message');      
      return $responseMsg;
    }

  }


   /**
   * consume servicio ws pasado por parametro.
   * @return String token $token: cadena de token solicitado
   */
  public function consumeGetToken(){
    
    $url = \Drupal::state()->get('token_ws_endpoint');
    $user = \Drupal::state()->get('token_ws_user');
    $pass = \Drupal::state()->get('token_ws_pass');
    $method = 'GET';
    $options = [
      'auth' => [$user,$pass],
    ];
   
    try {
      
      $response = $this->consumeService('Solicitud token',$method,$url,$options);
      
      if ($response->ErrorRest->errorCode == 200) {       
        return $response->token;
      }
      else{
        return $response->errorMsg;  
      }      
    }
    catch (RequestException $e) {
      $response->errorMsg = \Drupal::state()->get('error_message');      
      return $response;
    }

  }


  /**
   * consume servicio ws para obtener datos de usuario.
   * @param String $tipoDocumento: Tipo de documento de usuario
   * @param String $documento: numero documento
   * @return array $habeas_info: Información de usuario
   */
  public function consumeGetUser($tipoDocumento, $documento){

    //array con datos para almacenar en log
    $wsLogData = array('tipo_documento' => $tipoDocumento,'documento' => $documento);
    $wsLogData['servicio_ws'] = 'ClienteUnico/Cliente';

    //Array cn body para el request del ws
    $wsRequestData = array('tipoIdentificacion' => $tipoDocumento,'numeroIdentificacion' => $documento, 'usuario' => '1x1movilexito' , 'sistema' => 'CLIFRE');
    $wsRequest['Cuerpo'] = $wsRequestData;
    $wsRequestJson = json_encode($wsRequest);

    $url = \Drupal::state()->get('queryuser_ws_endpoint');    
    $method = 'POST';    
   
    $options = [
      'body' => $wsRequestJson,             
    ];

    try {
    
      $response = $this->consumeService('Consulta cliente',$method,$url,$options);

      \Drupal::logger('movilexito_ws')->error(json_encode($response));
      //no se encuenta usuario en sistema
      if(empty($response)){        
          $message = \Drupal::state()->get('nouser_message');
          $wsLogData['resultado_ws'] = 'vacio';
          $wsLogData['resultado_ws_detalle'] = 'No se encuentra usuario en sistema';
          $this->saveWsLog($wsLogData);
          $habeas_info->errorMsg = $message;
          return $habeas_info;
      }

      if(isset($response->Cuerpo[0])){
        $habeas_info = $response->Cuerpo[0];        
      }elseif(isset($response->errorMsg)){
        $habeas_info = $response;
      }
      return $habeas_info;
   
    }
    catch (RequestException $e) {
      watchdog_exception($e);
      \Drupal::logger('movilexito_ws')->error('fallo user');
      $habeas_info->errorMsg = \Drupal::state()->get('error_message');
      return $habeas_info;
    }

  }

  /**
   * consume servicio ws para validar linea de usuario
   * @param String $linea: linea nueva a validar
   * @param String $tipoDocumento: Tipo de documento de usuario
   * @param String $documento: numero documento
   * @return array $response: respuesta de validacoin de linea
   */
  public function consumeValidateUserNumber($linea,$tipoDocumento, $documento){

    //array con datos para almacenar en log
    $wsLogData = array('tipo_documento' => $tipoDocumento,'documento' => $documento);
    $wsLogData['servicio_ws'] = 'APP/MovilExito/V1/Consulta?msisdn';
   
    
    $url = \Drupal::state()->get('queryvalidatenumber_ws_endpoint').$linea;    
    $method = 'GET';    
    $token = $this->consumeGetToken();

    if(isset($token->errorMsg)){      
      return $token->errorMsg;
    }

    $options = [      
      'headers' => [
        'Content-Type' => 'application/json','Accept' => 'application/json', 'Authorization' => 'Bearer '.$token,        
      ]             
    ];

    try {

    
      $response = $this->consumeService('Validación número',$method,$url,$options);

     if(empty($response)){
        $message = \Drupal::state()->get('linea_no_activa');
        $wsLogData['resultado_ws'] = 'vacio';
        $wsLogData['resultado_ws_detalle'] = 'NO se encuentra linea en sistema';
        $this->saveWsLog($wsLogData);
        $response->errorMsg = $message;        
      }

      return $response;

    }
    catch (RequestException $e) {
      watchdog_exception($e);
      $response->errorMsg = \Drupal::state()->get('error_message');      
      return $response;
    }

  }


  /**
   * consume servicio ws para vaidar linea de usuario
   * @param String $linea: numero celular a verificar
   * @param String $tipoDocumento: Tipo de documento de usuario
   * @param String $documento: numero documento
   * @return array $response: respuesta de validacoin de linea
   */
  public function consumeSaveNumber($linea,$tipoDocumento, $documento){

    //array con datos para almacenar en log
    $wsLogData = array('tipo_documento' => $tipoDocumento,'documento' => $documento);
    $wsLogData['servicio_ws'] = '/LineaFavorita/v1';

    $url = \Drupal::state()->get('saveline_ws_endpoint');    
    $method = 'POST';    
    $token = $this->consumeGetToken();

    if(isset($token->errorMsg)){
      return $token->errorMsg;
    }
      

      //Array cn body para el request del ws
    $wsRequestData = array('tipoDocumento' => $tipoDocumento,'documento' => $documento, 'numeroFavorito' => $linea , 'tipoSistema' => 1);    
    $wsRequestJson = json_encode($wsRequestData);

    $options = [      
      'headers' => [
        'Content-Type' => 'application/json','Accept' => 'application/json', 'Authorization' => 'Bearer '.$token,        
      ],
      'body' => $wsRequestJson,             
    ];

    try {

      $response = $this->consumeService('Guardar número',$method,$url,$options);

       if(empty($response)){
        $message = \Drupal::state()->get('error_message');
        $wsLogData['resultado_ws'] = 'vacio';
        $wsLogData['resultado_ws_detalle'] = 'error de ws';
        $this->saveWsLog($wsLogData);
        $response->errorMsg = $message;  
        
      }      

      return $response;
    }
    catch (RequestException $e) {
      watchdog_exception($e);
    }

  }

  /**
   * consume servicio ws para obtener datos de usuario.
   * @param array $wsLogData: array con datos del consumo para almacenamiento en DB   
   */
  function saveWsLog($wsLogData){
    
    $ip = \Drupal::request()->getClientIp();
    $wsLogData['user_ip'] = $ip; 
    $wsLogData['fecha_actualizacion'] = date('Y-m-d h:i:s');

    \Drupal::database()->insert('exito_me_ws_log')
      ->fields([
        'tipo_documento',  // FIELD_1.
        'documento',  // FIELD_2.
        'servicio_ws',
        'resultado_ws',  // FIELD_3.
        'resultado_ws_detalle',
        'user_ip',
        'fecha_actualizacion',
      ])
      ->values($wsLogData)
      ->execute();
  }



}



<?php
namespace Drupal\movilexito_pqr\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\recaptcha\Model\RecaptchaModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\movilexito_pqr\Controller\MovilExitoPQRController;
/**
 * Defines a translation edit form.
 */
class AddPQRForm extends FormBase
{

    protected $database;

    /**
     *
     * @var \Drupal\recaptcha\Model\RecaptchaModel
     */
    protected $recaptchaModel;

    /**
     * Constructs a new PaymentController object.
     */
    public function __construct(RecaptchaModel $recaptcha)
    {
        $this->recaptchaModel = $recaptcha;
        $this->database = \Drupal::database();
    }

    /**
     *
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('movilexito_pqr.recaptcha')
        );
    }

    /**
     *
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'movilexito_pqr_add_form';
    }

    /**
     *
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = array(
            '#attributes' => array('enctype' => 'multipart/form-data') 
        );
        $form['user_data'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Ingreso de Información de la PQR')
        ];
        
        $form['user_data']['operator'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre completo del operador.'),
            '#required' => TRUE,
            '#default_value' => 'Movil Éxito',
        ];
        
        $form['user_data']['pqr'] = [
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => $this->t('¿Usted quiere presentar una petición,queja/reclamo o recurso?'),
            '#options' => [
                'peticion' => 'Petición',
                'queja' => 'Queja',
                'recurso_reposicion' => 'Recurso Reposición',
                'recurso_reposicion_apelacion' => 'Recurso Reposición Subsidio de Apelación'
            ]
        ];
                
        $form['user_data']['company_name'] = [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => $this->t('¿Cuál es su nombre o la razón social de su empresa?')
        ];
        
        $form['user_data']['last_name'] = [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => $this->t('¿Cuáles son sus apellidos?')
        ];
        
        $form['user_data']['document_type'] = [
            '#type' => 'select',
            '#title' => $this->t('¿Cuál es el tipo de su documento de identidad o el de su empresa?'),
            '#required' => TRUE,
            '#options' => [
                'cc' => 'Cedula de ciudadanía',
                'nit' => 'NIT',
                'ce' => 'Cedula de extranjería',
                'pp' => 'Pasaporte',
            ],
            '#default_value' => 'cc'
        ];
        
        $form['user_data']['document_number'] = [
            '#type' => 'textfield',
            '#title' => $this->t('¿Cuál es el número de su documento de identidad o el de su empresa?'),
            '#required' => TRUE
        ];
        
        $form['user_data']['contact_email'] = [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => $this->t('¿Cuál es el correo electrónico al cual quiere que llegue la respuesta?')
        ];
        
        
        $form['user_data']['contact_phone'] = [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => $this->t('¿Cuál es el número de teléfono de Contacto?')
        ];
        
        $form['user_data']['client_info'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Información del ticket')
        ];
        
        $form['user_data']['client_info']['objeto_pqr'] = [
            '#type' => 'textarea',
            '#required' => TRUE,
            '#attributes' => [
                'maxlength' => 1500
            ],
            '#title' => $this->t('¿Cuál es el objeto de su petición, queja/reclamo o recurso? (Máx. 1500 caracteres)')
        ];
        
        $form['user_data']['client_info']['hechos_pqr'] = [
            '#type' => 'textarea',
            '#required' => TRUE,
            '#attributes' => [
                'maxlength' => 1500
            ],
            '#title' => $this->t('¿Cuáles son los hechos en que fundamenta la petición, queja/reclamo o recurso? (Máx. 1500 caracteres)')
        ];

        $extensions = \Drupal::state()->get('pqr_extensions');
        $extensions = str_replace(',', ',.', $extensions);
        $form['user_data']['client_info']['content_files'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Adjuntar Archivos'),
            '#description' => $this->t('Nota: Solamente se permiten archivos en formato .'.$extensions),
        ];
        
        $form['user_data']['client_info']['content_files']['file'] = [
            '#type' => 'file',
            '#title' => $this->t('File'),
            '#title_display' => 'invisible',
            '#attributes' => [
                'accept' => ".".$extensions
            ],
        ];
                
        $form['user_data']['actions'] = [
            '#type' => 'actions'
        ];
        $form['user_data']['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Enviar')
        ];
        
        $form['#attached']['library'][] = 'movilexito_pqr/movilexito_pqr';
        $this->recaptchaModel->g_add_captcha($form, $form['user_data']['actions'], 'AddPQRForm');
        return $form;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        //captcha
        $response_captcha = $this->recaptchaModel->g_validate_submission();
        if(! $response_captcha){
            $form_state->setErrorByName('captcha', t('El captcha es obligatorio, por favor completelo'));
        }
        
        
        //contact_email
        if (!empty($form_state->getValue('contact_email'))) {
            if (!filter_var($form_state->getValue('contact_email'), FILTER_VALIDATE_EMAIL)) {
                $form_state->setErrorByName('contact_email', t('Verifique su correo electrónico, no es válido.'));
            }
            
        }

        //file
        $config_size = \Drupal::state()->get('pqr_size');
        $config_size = explode(' ', $config_size);
        $size = ($config_size[1] == 'MB') ? $config_size[0]*1024*1024 : $config_size[0]*1024 ;

        $extensions = \Drupal::state()->get('pqr_extensions');
        $extensions = str_replace(',', ' ', $extensions);
        $validators = array( 'file_validate_extensions' => array($extensions), 'file_validate_size' => array($size),FILE_EXISTS_RENAME );

        $path = "public://movilexito_pqr";
        file_prepare_directory($path, FILE_CREATE_DIRECTORY);

        $file = file_save_upload('file', $validators, $path);
        if ($file[0]) {
            $file[0]->setPermanent();
            $data['file'] = $file[0]->getFilename();
            $form_state->setValue('file',$file[0]->getFilename());
        }

    }

    /**
     *
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $data = $form_state->getValues();
        $pqr = new MovilExitoPQRController;

        $send = $pqr->enviar_pqr($data);
        if ($send) {
            drupal_set_message('Se a procesado correctamente su solicitud, gracias por comunicarse.');
            if (!empty($data['file'])) {
                if(file_exists('public://movilexito_pqr/'.$data['file']))
                {
                    unlink('public://movilexito_pqr/'.$data['file']);
                }
            }
            
        }else{
            $this->insertCrnEmails($params);
            drupal_set_message('Lo sentimos en este momento no se puede procesar su solicitud, por favor comuniquese a las líneas: *999 o 018000517677', 'error');
        }
    }

  

    public function getStatesSelect()
    {
        $result = [];
        try {
            $result = $this->database->select('movilexito_states', 'mes')
                ->fields('mes')
                ->execute()
                ->fetchAllKeyed(0, 2);
        } catch (\Exception $ex) {
            watchdog_exception('MovilExito Add PQR', $ex);
            return $result;
        }
        
        return $result;
    }

    public function getCitiesByStateSelect($did)
    {
        $result = [];
        try {
            $result = $this->database->select('movilexito_cities', 'mec')
                ->fields('mec')
                ->condition('did', $did)
                ->execute()
                ->fetchAllKeyed(0, 2);
        } catch (\Exception $ex) {
            watchdog_exception('MovilExito Add PQR', $ex);
            return $result;
        }
        
        return $result;
    }

    public function getCitiesByState(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        $renderer = \Drupal::service('renderer');
        $state_id = $form_state->getValue('state');
        $cities = $this->getCitiesByStateSelect($state_id);
        $form['user_data']['city']['#options'] = $cities;
        $form['user_data']['city']['#default_value'] = NULL;
        
        $response->addCommand(new ReplaceCommand('#cities-wrapper', $renderer->render($form['user_data']['city'])));
        return $response;
    }

    public function insertCrnEmails($params = array())
    {
        $result = [];
        try {
            
            $result = $this->database->insert('movilexito_crn_email')
                ->fields([
                'crn_email' => \Drupal::state()->get('pqr_emails'),
                'datos' => json_encode($params),
                'created' => REQUEST_TIME,
            ])
            ->execute();
        } catch (\Exception $ex) {
            watchdog_exception('MovilExito Add PQR', $ex);
            return $result;
        }
        
        return $result;
    }

    public function getOtherFile(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        $renderer = \Drupal::service('renderer');
        $form['user_data']['client_info']['files'] = [
            '#type' => 'file'
        ];
        $files = $form_state->getValue('files');
        $files[] = NULL;
        $form_state->setValue('files', $files);
        $response->addCommand(new ReplaceCommand('#more-files-wrapper', $renderer->render($form['user_data']['client_info'])));
        return $response;
    }
}



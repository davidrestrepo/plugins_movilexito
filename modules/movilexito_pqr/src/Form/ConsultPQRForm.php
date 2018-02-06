<?php
namespace Drupal\movilexito_pqr\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\recaptcha\Model\RecaptchaModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a translation edit form.
 */
class ConsultPQRForm extends FormBase
{

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
    }

    /**
     *
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static($container->get('movilexito_pqr.recaptcha'));
    }

    /**
     *
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'movilexito_pqr_consult_form';
    }

    /**
     *
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['user_data'] = [
            '#type' => 'fieldset',
            '#description' => '',
            '#title' => $this->t('Consultar información de la petición.')
        ];
        
        $form['user_data']['cun'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Número CUN*'),
            '#description' => $this->t('Ingrese el número CUN entregado al realizar la petición.'),
            '#attributes' => [
                'placeholder' => 'CUN:'
            ],
            '#maxlength' => 15
        ];
        $form['user_data']['actions'] = [
            '#type' => 'actions'
        ];
        $form['user_data']['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Consultar'),
            '#ajax' => [
                'callback' => 'Drupal\movilexito_pqr\Form\ConsultPQRForm::submit_form_callback',
                'event' => 'click'
            ]
        ];
        
        $form['#attached']['library'][] = 'movilexito_pqr/movilexito_pqr';
        $this->recaptchaModel->g_add_captcha($form, $form['user_data']['actions'], 'consultPQRForm');
        return $form;
    }

    /**
     * Callback for opening the modal form.
     */
    public function submit_form_callback(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        $error = FALSE;
        $html_error = '';
        
        $options = [
            'width' => '500'
        ];
        $cun = $form_state->getValue('cun');
        $cun = trim($cun);
        $recaptchaModel = new RecaptchaModel();
        $response_captcha = $recaptchaModel->g_validate_submission();
        if (! $response_captcha) {
            $error = TRUE;
            $script = $recaptchaModel->reset_captcha('consultPQRForm');
            $html_error .= t('El captcha es obligatorio!') . '<script>' . $script . '</script>';
        } elseif (empty($cun)) {
            $error = TRUE;
            $html_error .= t('El número del CUN es requerido!') . '<script>' . $script . '</script>';
        } elseif (strlen($cun) != 15) {
            $error = TRUE;
            $html_error .= t('El número del CUN debe tener 15 dígitos!') . '<script>' . $script . '</script>';
        }
        
        if ($error) {
            $html_error = '<div role="contentinfo" aria-label="Mensaje de error" class="messages messages--error"><div role="alert">' . $html_error . '</div></div>';
            $response->addCommand(new CssCommand('.message-info', [
                'display' => 'inline-block' /* 'background' => '#ff5050' */
            ]));
            $response->addCommand(new HtmlCommand('.message-info', $html_error));
            return $response;
        }
        
        $response->addCommand(new OpenModalDialogCommand(t("Error en la consulta"), t("Lo sentimos, en este momento no podemos realizar esta transacción, por favor intente más tarde o comuniquese a las líneas: *999 o 018000517677"), $options));
        sleep(3);
        return $response;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {}

    /**
     *
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {}
}

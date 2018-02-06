<?php

namespace Drupal\recaptcha\Model;

class RecaptchaModel{
	/**
	* @file
	* Functions for providing support with Google reCaptcha.
	*/

	var $tune; 

	function __construct(){
		$config = \Drupal::config('recaptcha.settings');
		$this->tune = [
			'public_key' => $config->get('public_key'), 
			'secret_key' => $config->get('secret_key'), 
			'settings' => [
				'language' => $config->get('language'),
				'widget_size' => $config->get('widget_size'),
				'widget_theme' => $config->get('widget_theme'),
			]
		];
	}

	public function reset_captcha($form_id){
		$captcha_form_name = 'google_recaptcha_' . $form_id;
		$captcha_js = 'grecaptcha.reset(Drupal.behaviors.'.$captcha_form_name.', {
			"sitekey" : "' . $this->tune['public_key'] . '"});';
			return $captcha_js;
	}

	/**
	* Add captcha
	*/
	public function g_add_captcha(array &$form, &$form_content, $form_id) {

		$captcha_form_name = 'google_recaptcha_' . $form_id;
		$captcha_container = '<div id="' . $captcha_form_name . '"></div>';

		$widget_size = $form_id = $this->tune['settings']['widget_size'];
		$widget_theme = $this->tune['settings']['widget_theme'];

		$captcha_js = 'Drupal.behaviors.'.$captcha_form_name.' = grecaptcha.render("' . $captcha_form_name . '", {
			"sitekey" : "' . $this->tune['public_key'] . '", 
			"size" : "' . $widget_size . '", 
			"theme" : "' . $widget_theme . '"});
		';

		$recaptcha_load = 'var google_recaptcha_onload = function() {' . $captcha_js . '};';
		$form['#attached']['library'][] = 'recaptcha/recaptcha';
		// $form['#attached']['library'][] = $recaptcha_load;
		$form['#attached']['html_head'][] = [
			// The data.
        [
          // The HTML tag to add, in this case a <script> tag.
          '#tag' => 'script',
          '#attributes' => array(
            'language' => "javascript",
            'type' => "text/javascript",
          ),
          '#value' => $recaptcha_load,
        ],
        // A key, to make it possible to recognize this HTML <HEAD>
        // element when altering.
        'jquery-define'
		];

		$form_content['#prefix'] = empty($form_content['#prefix']) ? $captcha_container : $captcha_container . $form_content['#prefix'];
		// $form['#validate'][] = 'g_validate_submission';
	}

	/**
	* Additional validation function for protected form
	* Here we ask from Google - is this submission ok?
	*
	* @param $form
	* @param $form_state
	*/
	public function g_validate_submission() {
		$secret = !empty($this->tune['secret_key']) ? $this->tune['secret_key'] : '';
		$response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
		$remote_ip = \Drupal::request()->getClientIp();

		$answer = $this->g_ask_google($secret, $response, $remote_ip);
		return $answer;
	}

	/**
	* Ask from Google is this submission ok
	* https://developers.google.com/recaptcha/docs/verify
	*
	* @param $secret
	* @param $response
	* @return bool
	*/
	public function g_ask_google($secret, $response, $remoteip) {
		$answer = FALSE;

		$request_data = array(
			'secret' => $secret,
			'response' => $response,
			'remoteip' => $remoteip,
		);

		$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);

		$response = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($response, TRUE);

		if ($response['success'] == FALSE) {
			$error_codes = array(
				  'missing-input-secret' => 'The secret parameter is missing.',
				  'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
				  'missing-input-response' => 'The response parameter is missing.',
				  'invalid-input-response' => 'The response parameter is invalid or malformed.',
			 );

			if (!empty($response['error-codes']) && $this->tune['settings']['write_log'] == 1) {
				  $log_vars = array(
				    '@error' => $error_codes[$response['error-codes'][0]],
				    '@remoteip' => $remoteip,
				    );
				  watchdog('Google reCAPTCHA', 'Google service returned error "@error". Site visitor address: @remoteip', $log_vars, WATCHDOG_WARNING);
			}
		}
		elseif ($response['success'] == TRUE) {
			$answer = TRUE;
		}

		return $answer;
	}
}
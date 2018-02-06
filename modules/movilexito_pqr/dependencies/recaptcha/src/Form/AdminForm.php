<?php

namespace Drupal\recaptcha\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase; 

class AdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recaptcha_admin_form';
  }

	/** 
	* {@inheritdoc}
	*/
	protected function getEditableConfigNames() {
		return [
			'recaptcha.settings',
		];
	}

  /**
   * {@inheritdoc}
   */
	public function buildForm(array $form, FormStateInterface $form_state) {
		$config = $this->config('recaptcha.settings');

		$form['content'] = [
			'#type' => 'fieldset',
			'#title' => $this->t('Configuración de credenciales Google Recaptcha'),
			'#prefix' => $this->t('Both keys You can create on <a href="https://www.google.com/recaptcha" target="_blank">Google reCAPTCHA site.</a><br/>Be careful if You have several sites - check that this pair of keys exactly for THIS site.')
		];

		$form['content']['public_key'] = array(
			'#title' => $this->t('Google reCAPTCHA public key'),
			'#type' => 'textfield',
			'#default_value' => empty($config->get('public_key')) ? '' : $config->get('public_key'),
			'#size' => 40,
			'#maxlength' => 40,
			'#required' => TRUE,
		);
		$form['content']['secret_key'] = array(
			'#title' => $this->t('Google reCAPTCHA secret key'),
			'#type' => 'textfield',
			'#default_value' => empty($config->get('secret_key')) ? '' : $config->get('secret_key'),
			'#size' => 40,
			'#maxlength' => 40,
			'#required' => TRUE,
		);
		$form['content']['language'] = array(
			'#title' => $this->t('Google reCAPTCHA language'),
			'#description' => $this->t('Enter the language code that can be found here: https://developers.google.com/recaptcha/docs/language'),
			'#type' => 'textfield',
			'#default_value' => empty($config->get('language')) ? 'en' : $config->get('language'),
			'#size' => 10,
			'#maxlength' => 10,
		);
		$form['content']['widget_size'] = array(
			'#title' => $this->t('Widget size'),
			'#type' => 'select',
			'#options' => array('normal' => 'normal', 'compact' => 'compact'),
			'#default_value' => $config->get('widget_size'),
		);
		$form['content']['widget_theme'] = array(
			'#title' => $this->t('Widget theme'),
			'#type' => 'select',
			'#options' => array('light' => 'light', 'dark' => 'dark'),
			'#default_value' => $config->get('widget_theme'),
		);
	  	return parent::buildForm($form, $form_state); 
  	}

	public function validateForm(array &$form, FormStateInterface $form_state) {}

	public function submitForm(array &$form, FormStateInterface $form_state) {
		parent::submitForm($form, $form_state);
		\Drupal::configFactory()->getEditable('recaptcha.settings')
		->set('public_key', $form_state->getValue('public_key'))
		->set('secret_key', $form_state->getValue('secret_key'))
		->set('widget_size', $form_state->getValue('widget_size'))
		->set('widget_theme', $form_state->getValue('widget_theme'))
		->set('language', $form_state->getValue('language'))
		->save();
	} 
}
<?php

namespace Drupal\recaptcha\Controller;

use Drupal\Core\Controller\ControllerBase;

class RecaptchaController extends ControllerBase {


	public function adminForm(){
		$formName = 'Drupal\recaptcha\Form\AdminForm';
    	$form = \Drupal::formBuilder()->getForm($formName);
		return [
			'form' => $form,
		];
	}


}
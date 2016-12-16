<?php
/**
 * @file
 * Contains \Drupal\adw_search\Form\RobotsEdit
 */

namespace Drupal\adw_search\Form;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

class RobotsEditForm extends ConfigFormBase{
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'edit_robots_file';
	}
	
	  /** 
	* {@inheritdoc}
	*/
	protected function getEditableConfigNames() {
		return [
		  'adw_search.settings',
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
		$config = $this->config('adw_search.settings');

		$form['robots_file_contents'] = array(
		  '#type' => 'textarea',
		  '#title' => $this->t('Robots File Contents'),
		  '#default_value' => $config->get('robots_file_contents'),
		);  

		return parent::buildForm($form, $form_state);
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
		$this->config('adw_search.settings')
		  ->set('robots_file_contents', $form_state->getValue('robots_file_contents'))
		  ->save();

		parent::submitForm($form, $form_state);
	}
}

?>
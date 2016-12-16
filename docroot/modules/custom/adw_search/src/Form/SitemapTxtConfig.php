<?php
/**
 * @file
 * Contains \Drupal\adw_search\Form\RobotsEdit
 */

namespace Drupal\adw_search\Form;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

class SitemapTxtConfig extends ConfigFormBase{
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'edit_sitemap_txt';
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

		//get node types
		$nodeTypes = node_type_get_types();
		$nodeOptions = array();
		foreach($nodeTypes as $nodeTypeMachineName => $nodeType){
			$nodeOptions[$nodeTypeMachineName] = $nodeType->get('name');
		}

		//-----------------------
		// Let's build this form
		//-----------------------
		$config = $this->config('adw_search.settings');

		$form['sitemap_txt_content_types'] = array(
		  '#type' => 'checkboxes',
		  '#title' => $this->t('Include these content types in the sitemap.txt'),
		  '#default_value' => $config->get('sitemap_txt_content_types'),
		  '#options' => $nodeOptions,
		);  
		
		$form['sitemap_txt_additional_pages'] = array(
		  '#type' => 'textarea',
		  '#title' => $this->t('Additional Pages to include on sitemap '),
		  '#default_value' => $config->get('sitemap_txt_additional_pages'),
		  '#description' => 'Enter one page per line. Include the leading slash.',
		);
		
		$form['sitemap_txt_omit_pages'] = array(
		  '#type' => 'textarea',
		  '#title' => $this->t('Pages to omit from sitemap'),
		  '#default_value' => $config->get('sitemap_txt_omit_pages'),
		  '#description' => 'Enter one page per line. Include the leading slash.',
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
		  ->set('sitemap_txt_content_types', $form_state->getValue('sitemap_txt_content_types'))
		  ->set('sitemap_txt_additional_pages', $form_state->getValue('sitemap_txt_additional_pages'))
		  ->set('sitemap_txt_omit_pages', $form_state->getValue('sitemap_txt_omit_pages'))
		  ->save();

		parent::submitForm($form, $form_state);
	}
}

?>
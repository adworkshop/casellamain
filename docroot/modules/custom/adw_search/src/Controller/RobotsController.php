<?php
/**
 * @file
 * Contains \Drupal\adw_search\Controller\RobotsController.
 */

namespace Drupal\adw_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class RobotsController extends ControllerBase {

	/**
	* {@inheritdoc}
	*/
	public function content() {
	
		//get robots file contents
		$config = \Drupal::config('adw_search.settings');
	
		//create response object
		$response = new Response();
		$response->setContent($config->get('robots_file_contents'));
		$response->headers->set('Content-Type', 'text/plain');
		return $response;
	}

}

/*
use Symfony\Component\HttpFoundation\Response;

$response = new Response();
$response->setContent('test');
$response->headers->set('Content-Type', 'text/plain');
return $response;
*/
?>
<?php
/**
 * @file
 * Contains \Drupal\adw_search\Controller\SitemapTxtController.
 */

namespace Drupal\adw_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class SitemapTxtController extends ControllerBase {

	/**
	* {@inheritdoc}
	*/
	public function content() {
	
		$sitemapEntries = array();
	
		//get settings
		$config = \Drupal::config('adw_search.settings');
		$contentTypeSelection = $config->get('sitemap_txt_content_types');
		$pathsToRemove = explode("\n",$config->get('sitemap_txt_omit_pages'));
		foreach($pathsToRemove as $key => $value){
			$pathsToRemove[$key] = trim($value);
		}
		
		//loop over content types
		foreach($contentTypeSelection as $contentTypeMachineName => $contentTypeSelectedValue){
			//if this content type is selected for output
			if($contentTypeSelectedValue !== 0){
				$query = \Drupal::entityQuery('node')->condition('type', $contentTypeMachineName);
				$nidsOfThisType = $query->execute();
				//loop over nids in this content type
				foreach($nidsOfThisType as $nid){
					$thisPath = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$nid);
					//if this has a legit path and not just the node/* path
					//and it's not in our list of paths to remove
					if($thisPath != '/node/'.$nid && !in_array($thisPath,$pathsToRemove)){
						$sitemapEntries[$thisPath] = $thisPath;
					}
				}
			}
		}
	
		//add the ones from admin settings
		$pathsToAdd = explode("\n",$config->get('sitemap_txt_additional_pages'));
		foreach($pathsToAdd as $path){
			$sitemapEntries[trim($path)] = trim($path);
		}
	
		global $base_url;
		//create response object
		$response = new Response();
		$response->setContent($base_url.implode("\n".$base_url,$sitemapEntries));
		$response->headers->set('Content-Type', 'text/plain');
		return $response;
	}

}

?>
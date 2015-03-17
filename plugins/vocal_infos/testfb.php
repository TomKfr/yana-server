<?php


define('FACEBOOK_SDK_V4_SRC_DIR', '/var/www/yana-server/plugins/vocal_infos/src/Facebook/');
require __DIR__ . '/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookCanvasLoginHelper;

			$sentence = "Phrase de base ...\n";


			FacebookSession::setDefaultApplication('944393428906526','3c360d7d962d47fccd2d84dcb2c10273');

			// Use one of the helper classes to get a FacebookSession object.
			//   FacebookRedirectLoginHelper
			//   FacebookCanvasLoginHelper
			//   FacebookJavaScriptLoginHelper
			// or create a FacebookSession with a valid access token:
			$session = new FacebookSession('CAANa67rbsh4BAGpi7lsldHmrDT4vk50PYOa9hSU5IPxGigotjSI7TrQX4PhbMAd9pjiEijfU8KFG00eeSZBX5NIzXS2dxqYQVqRJhuh0bWrMzP5OyNNdydQg6drMRDAib0fcpy7WSfObEZBgbEVpEflb0UQvgop2AVCavEihJWZCVzfBOJZCrbE7ZAri0xXPjPJFPY0o6gqG7nLy7ROxp');
			
			$ucount = 0;

			try {

			  	$request = new FacebookRequest(
				$session,
  				'GET',
  				'/me/notifications'
				);

				$response = $request->execute();
				$graphObject = $response->getGraphObject();

				$tmp = $graphObject->getProperty('summary');
				if(isset($tmp)) $ucount = $tmp->getProperty('unseen_count');
				else $ucount = 0;

			} catch (FacebookRequestException $e) {
			  $error = $e->getMessage()."\n";
			} catch (\Exception $e) {
			  $error = "Erreur inconnue\n";
			}

			$sentence = "Vous avez ".intval($ucount)." notifications non lues.\n";
			echo $sentence;
			echo $error;


?>
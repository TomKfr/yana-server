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
			$session = new FacebookSession('CAANa67rbsh4BAKPG5zbjbxhGpb80bKTJ1nYDChZAoveOI56y7ZBwRDDT2qBEY9CanZCnMmZCIrA6nZB5bwrGVImCr7OmJHoq8u8fEOsJT2p8KOtqZBn4fysLLSFtRjQvhTdCYZCYFZBdKhuw35PCcwS7gHqeiQcUK0nKfybQj3n5MdSdN4xzdZCo9ZAb5MNvHmxt3bArOnZC2uk5K0rVZAA5OZCl4');
			
			$ucount = 0;

			// Get the GraphUser object for the current user:

			try {

			  $request = new FacebookRequest(
				$session,
  				'GET',
  				'/me/notifications'
				);
				$response = $request->execute();
				$graphObject = $response->getGraphObject();

				$ucount = $graphObject->getProperty('summary')->getProperty('unseen_count');
				echo "Notifications non lues : ".$graphObject->getProperty('summary')->getProperty('unseen_count')."\n";

			} catch (FacebookRequestException $e) {
			  $sentence = $e->getMessage()."\n";
			} catch (\Exception $e) {
			  $sentence = "Erreur inconnue";
			}

			$sentence = "Vous avez ".intval($ucount)." notifications non lues.";
			echo $sentence;

?>
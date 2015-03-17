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
			$session = new FacebookSession('CAANa67rbsh4BACHJlVWBpOWL6TZA0ZAkjtC7qb9opqAq1tZBrMJNs7nHZAHtRAZBQDkUxX70VRgBunmkJyVcHyxLba1fJsjAotoDUGmT71ZCeGUxm8RNCxIdBN3zmswiEdi3xRGOkELbm1CK5At00XmlPOSnU3E1pwJBBRczta9XP4vQ79YpNkW3zacyQ0JnL5FZCdE7ZBgh1ZA94MYquZA4DM');
			
			$ucount = 0;

			try {

				echo "step 1 \n";

			  	$request = new FacebookRequest(
				$session,
  				'GET',
  				'/me/notifications'
				);

			  	echo "step 2 \n";

				$response = $request->execute();
				$graphObject = $response->getGraphObject();

				$ucount = $graphObject->getProperty('summary')->getProperty('unseen_count');
				$sum = $graphObject->getProperty('summary')->getPropertyNames();
				foreach ($sum as $key) {
					echo $key."\n";
				}
				echo "Notifications non lues : ".$graphObject->getProperty('summary')->getProperty('unseen_count')."\n";

			} catch (FacebookRequestException $e) {
			  $error = $e->getMessage()."\n";
			} catch (\Exception $e) {
			  $error = "Erreur inconnue\n";
			}

			$sentence = "Vous avez ".intval($ucount)." notifications non lues.\n";
			echo $sentence;
			echo $error;


?>
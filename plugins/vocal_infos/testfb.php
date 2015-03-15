<?php


//define('FACEBOOK_SDK_V4_SRC_DIR', '/');
require __DIR__ . '/autoload.php';
 /*require_once( 'Facebook/FacebookSession.php' );
 require_once( 'Facebook/Entities/AccessToken.php' );
 require_once( 'Facebook/FacebookRedirectLoginHelper.php' );
 require_once( 'Facebook/FacebookRequest.php' );
 require_once( 'Facebook/FacebookResponse.php' );
 require_once( 'Facebook/FacebookSDKException.php' );
 require_once( 'Facebook/FacebookRequestException.php' );
 require_once( 'Facebook/FacebookAuthorizationException.php' );
 require_once( 'Facebook/GraphObject.php' );
 require_once( 'Facebook/GraphUser.php' );
 require_once( 'Facebook/HttpClients/FacebookStreamHttpClient.php');
 require_once( 'Facebook/HttpClients/FacebookHttpable.php');*/

use Facebook\FacebookSession;
			use Facebook\FacebookRequest;
			use Facebook\GraphUser;
			use Facebook\FacebookRequestException;

			$sentence = "Phrase de base ...";


			FacebookSession::setDefaultApplication('944393428906526','3c360d7d962d47fccd2d84dcb2c10273');

			// Use one of the helper classes to get a FacebookSession object.
			//   FacebookRedirectLoginHelper
			//   FacebookCanvasLoginHelper
			//   FacebookJavaScriptLoginHelper
			// or create a FacebookSession with a valid access token:
			$session = new FacebookSession('17fc188b32f067857b9ed93d3cf278b2');

			// Get the GraphUser object for the current user:

			try {
			  $me = (new FacebookRequest(
			    $session, 'GET', '/me'
			  ))->execute()->getGraphObject(GraphUser::className());
			  echo $me->getName();
			} catch (FacebookRequestException $e) {
			  $sentence = "Erreur de l'api graph";
			} catch (\Exception $e) {
			  $sentence = "Erreur inconnue";
			}

			echo($sentence);
?>
<?php
/*
@name Informations vocales
@author Valentin CARRUESCO <idleman@idleman.fr>
@link http://blog.idleman.fr
@licence CC by nc sa
@version 1.0.0
@description Permet la récuperations d'informations locales ou sur le web comme la météo, les séries TV, l'heure, la date et l'état des GPIO
*/

define('VOCALINFO_COMMAND_FILE','cmd.json');


function vocalinfo_vocal_command(&$response,$actionUrl){
	global $conf;

	$commands = json_decode(file_get_contents(Plugin::path().'/'.VOCALINFO_COMMAND_FILE),true);
	foreach($commands as $key=>$command){
		if($command['disabled']=='true') continue;
		$response['commands'][] = array(
		'command'=>$conf->get('VOCAL_ENTITY_NAME').' '.$command['command'],
		'url'=>$actionUrl.$command['url'],'confidence'=>($command['confidence']+$conf->get('VOCAL_SENSITIVITY'))
		);
	}

}

function vocalinfo_action(){
	global $_,$conf;

	switch($_['action']){
	
		case 'plugin_vocalinfo_save':
			$commands = json_decode(file_get_contents(Plugin::path().'/'.VOCALINFO_COMMAND_FILE),true);
			
			foreach($_['config'] as $key=>$config){
				$commands[$key]['confidence'] = $config['confidence'];
				$commands[$key]['disabled'] = $config['disabled'];
			}
			file_put_contents(Plugin::path().'/'.VOCALINFO_COMMAND_FILE,json_encode($commands));
			echo 'Enregistré';
		break;
	
		case 'vocalinfo_plugin_setting':
			$conf->put('plugin_vocalinfo_place',$_['weather_place']);
			$conf->put('plugin_vocalinfo_woeid',$_['woeid']);
			header('location:setting.php?section=preference&block=vocalinfo');
		break;

		case 'vocalinfo_sound':
			global $_;
			$response = array('responses'=>array(
										array('type'=>'sound','file'=>$_['sound'])
													)
								);
			$json = json_encode($response);
			echo ($json=='[]'?'{}':$json);
			break;
			
		case 'vocalinfo_devmod':
			$response = array('responses'=>array(
										array('type'=>'command','program'=>'C:\Program Files\Sublime Text 2\sublime_text.exe'),
										array('type'=>'talk','sentence'=>'Sublim text lancé.')
													)
								);


			$json = json_encode($response);
			echo ($json=='[]'?'{}':$json);
		break;

		case 'vocalinfo_gpio_diag':
			$sentence = '';
	
			$gpio = array('actif'=>array(),'inactif'=>array());
			for ($i=0;$i<26;$i++) {
				$commands = array();
				exec("/usr/local/bin/gpio read ".$i,$commands,$return);
				if(trim($commands[0])=="1"){
					$gpio['actif'][] = $i;
				}else{
					$gpio['inactif'][] = $i;
				}
			}
			if(count($gpio['actif'])==0){
				$sentence .= 'Tous les GPIO sont inactifs.';
			}else if(count($gpio['inactif'])==0){
				$sentence .= 'Tous les GPIO sont actifs.';
			}else{
				$sentence .= 'GPIO actifs: '.implode(', ', $gpio['actif']).'. GPIO inactifs: '.implode(', ', $gpio['inactif']).'.';
			}
			

			$response = array('responses'=>array(
										array('type'=>'talk','sentence'=>$sentence)
													)
								);


			$json = json_encode($response);
			echo ($json=='[]'?'{}':$json);
		break;
		case 'vocalinfo_commands':

			
			$actionUrl = 'http://'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
			$actionUrl = substr($actionUrl,0,strpos($actionUrl , '?'));
			$commands = array();
			Plugin::callHook("vocal_command", array(&$commands,$actionUrl));
			$sentence ='Je répond aux commandes suivantes: ';
			foreach ($commands['commands'] as $command) {
				$sentence .=$command['command'].'. ';
			}

			$response = array('responses'=>array(
										array('type'=>'talk','sentence'=>$sentence)
													)
								);

			$json = json_encode($response);
			echo ($json=='[]'?'{}':$json);
		break;
		case 'vocalinfo_meteo':
			global $_;
				if($conf->get('plugin_vocalinfo_woeid')!=''){
				$contents = file_get_contents('http://weather.yahooapis.com/forecastrss?w='.$conf->get('plugin_vocalinfo_woeid').'&u=c');
				$xml = simplexml_load_string($contents);
				if(	(isset($_['today'])))
				{
					$weekdays = $xml->xpath('/rss/channel/item/yweather:condition');
				}
				else
				{
					$weekdays = $xml->xpath('/rss/channel/item/yweather:forecast');
				}
				//Codes disponibles ici: http://developer.yahoo.com/weather/#codes
				$textTranslate = array(
										'Showers'=>'des averses',										
										'Tornado' => 'Attention: Tornade!',
										'Hurricane' => 'Attention: Ouragan!',
										'Severe thunderstorms' => 'Orages violents',
										'Mixed rain and snow' => 'Pluie et neiges',
										'Mixed rain and sleet' => 'Pluie et neige fondue',
										'Mixed snow and sleet' => 'Neige et neige fondue',
										'Freezing drizzle' => 'Bruine verglassant',
										'Drizzle' => 'Bruine',
										'Freezing rain' => 'Pluie verglassant',
										'Showers' => 'Averse',
										'Snow flurries' => 'Bourrasque de neige',
										'Light snow showers' => 'Averse de neige lègére',
										'Blowing snow' => 'Chasse neige',
										'Snow' => 'Neige',
										'Hail' => 'Grêle',
										'Sleet' => 'Neige fondue',
										'Dust' => 'Poussière',
										'Foggy' => 'Brouillard',
										'Smoky' => 'Fumée',
										'Blustery' => 'Froid et venteux',
										'Windy' => 'Venteux',
										'Cold' => 'Froid',
										'Cloudy' => 'Nuageux',
										'Fair' => 'Ciel dégagé',
										'Mixed rain and hail' => 'Pluie et grêle',
										'Hot' => 'Chaud',
										'Isolated thunderstorms' => 'Orages isolées',
										'Scattered showers' => 'Averse éparse',
										'Heavy snow' => 'Fortes chutes de neige',
										'Scattered snow showers' => 'Averse de neige éparse',
										'Thunderstorms' => 'Orages',
										'Thundershowers' => 'Grain sous orage violents',
										'Isolated thundershowers' => 'Grain sous orage isolées',
										'Not available' => 'Non disponible',
										'Scattered Thunderstorms' => 'Orages éparses',
										'Partly Cloudy'=>'Partiellement nuageux',
										'Mostly Sunny'=>'plutot ensoleillé',
										'Mostly Cloudy'=>'plutot Nuageux',
										'Light Rain'=>'Pluie fine',
										'Clear'=>'Temps clair',
										'Sunny'=>'ensoleillé',
										'Rain/Wind'=>'Pluie et vent',
										'Rain'=>'Pluie',
										'Wind'=>'Vent',
										'Partly Cloudy/Wind'=>'Partiellement nuageux avec du vent'
										);
				$dayTranslate = array('Wed'=>'mercredi',
										'Sat'=>'samedi',
										'Mon'=>'lundi',
										'Tue'=>'mardi',
										'Thu'=>'jeudi',
										'Fri'=>'vendredi',
										'Sun'=>'dimanche');
				$affirmation = '';

				foreach($weekdays as $day){

					if (substr($day['text'],0,2) == "AM")
					{
						$sub_condition = substr($day['text'],3);
						$condition = (isset($textTranslate[''.$sub_condition])?$textTranslate[''.$sub_condition]:$sub_condition)." dans la matinée";

					}
					elseif (substr($day['text'],0,2) == "PM") {
						$sub_condition = substr($day['text'],3);
						$condition = (isset($textTranslate[''.$sub_condition])?$textTranslate[''.$sub_condition]:$sub_condition)." dans l'après midi";
					 } 
					 elseif (substr($day['text'],-4) == "Late") {
					 	$sub_condition = substr($day['text'],0,-5);
					 	$condition = (isset($textTranslate[''.$sub_condition])?$textTranslate[''.$sub_condition]:$sub_condition)." en fin de journée";
					 }
					 else
					 {
					 	$condition = isset($textTranslate[''.$day['text']])?$textTranslate[''.$day['text']]:$day['text'];
					 }
					

					if(	(isset($_['today'])))
					{
						$affirmation .= 'Aujourd\'hui '.$day['temp'].' degrés, '.$condition.', ';
					}
					else
					{
						$affirmation .= $dayTranslate[''.$day['day']].' de '.$day['low'].' à '.$day['high'].' degrés, '.$condition.', ';
					}
				}
			}else{
				$affirmation = 'Vous devez renseigner votre ville dans les préférences de l\'interface oueb, je ne peux rien vous dire pour le moment.';
			}

				$response = array('responses'=>array(
										array('type'=>'talk','sentence'=>$affirmation)
													)
								);
				$json = json_encode($response);
				echo ($json=='[]'?'{}':$json);
		break;

		case 'vocalinfo_tv':
			global $_;

				libxml_use_internal_errors(true);

			
				
				$contents = file_get_contents('http://webnext.fr/epg_cache/programme-tv-rss_'.date('Y-m-d').'.xml');
				

				$xml = simplexml_load_string($contents);
				$emissions = $xml->xpath('/rss/channel/item');

				$focus = array();
				
				
				$time = time();
				$date = date('m/d/Y ',$time);
				$focusedCanals = array('TF1','France 2','France 3','France 4','Canal+','Arte','France 5','M6');
				foreach($emissions as $emission){
					$item = array();
					list($item['canal'],$item['hour'],$item['title']) = explode(' | ',$emission->title);
					$itemTime = strtotime($date.$item['hour']);
					if($itemTime>=$time-3600 && $itemTime<=$time+3600 && in_array($item['canal'], $focusedCanals)){
						if(	(isset($_['category']) && $_['category']==''.$emission->category) || !isset($_['category']) ){
							$item['category'] = ''.$emission->category;
							$item['description'] = strip_tags(''.$emission->description);
							$focus[$item['title'].$item['canal']][] = $item;
						}
					}
				}
			
				$affirmation = '';
				$response = array();

				foreach($focus as $emission){
						$nb = count($emission);
						$emission = $emission[0];
						$affirmation = array();
						$affirmation['type'] = 'talk';
						//$affirmation['style'] = 'slow';
						$affirmation['sentence'] = ($nb>1?$nb.' ':'').ucfirst($emission['category']).', '.$emission['title'].' à '.$emission['hour'].' sur '.$emission['canal'];
						$response['responses'][] = $affirmation;
				}
				
				$json = json_encode($response);
				echo ($json=='[]'?'{}':$json);


		break;
		case 'vocalinfo_hour':
			global $_;
				$affirmation = 'Il est '.date('H:i');
				$response = array('responses'=>array(
										array('type'=>'talk','sentence'=>$affirmation)
													)
								);
				$json = json_encode($response);
				echo ($json=='[]'?'{}':$json);
		break;
		case 'vocalinfo_day':
			global $_;
				$affirmation = 'Nous sommes le '.date('d/m/Y');
				$response = array('responses'=>array(
										array('type'=>'talk','sentence'=>$affirmation)
													)
								);
				$json = json_encode($response);
				echo ($json=='[]'?'{}':$json);
		break;
		case 'vocalinfo_wikipedia':
			global $_;
			
				$url = 'http://fr.wikipedia.org/w/api.php?action=parse&page='.$_['word'].'&format=json&prop=text&section=0';
				$ch = curl_init($url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr-FR; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1" ); // required by wikipedia.org server; use YOUR user agent with YOUR contact information. (otherwise your IP might get blocked)
				$c = curl_exec($ch);

				$json = json_decode($c);

				$content = $json->{'parse'}->{'text'}->{'*'}; // get the main text content of the query (it's parsed HTML)

				$affirmation = strip_tags(str_replace('&#160;', ' ', $content)); // '&#160;' is a space, but is not recognized by yana trying to read "160"
				
				$response = array('responses'=>array(
									array('type'=>'talk','sentence'=>$affirmation)
												)
								);
				$json = json_encode($response);
				echo ($json=='[]'?'{}':$json);
		break;
		case 'vocalinfo_mood':
			global $_;
				$possible_answers = array(
					'parfaitement'
					,'ça pourrait aller mieux'
					,'ça roule mon pote !'
					,'nickel'
					,'pourquoi cette question ?'
				);
				
				$affirmation = $possible_answers[rand(0,count($possible_answers)-1)];
				$response = array('responses'=>array(
										array('type'=>'talk','sentence'=>$affirmation)
													)
								);
				$json = json_encode($response);
				echo ($json=='[]'?'{}':$json);
		break;
		case 'vocalinfo_facebook':

			require_once('require_fb.php');
			global $_;

			$ucount = 0;
			$error;

			FacebookSession::setDefaultApplication('944393428906526','3c360d7d962d47fccd2d84dcb2c10273');

			// Use one of the helper classes to get a FacebookSession object.
			//   FacebookRedirectLoginHelper
			//   FacebookCanvasLoginHelper
			//   FacebookJavaScriptLoginHelper
			// or create a FacebookSession with a valid access token:
			$session = new FacebookSession('CAANa67rbsh4BAGpi7lsldHmrDT4vk50PYOa9hSU5IPxGigotjSI7TrQX4PhbMAd9pjiEijfU8KFG00eeSZBX5NIzXS2dxqYQVqRJhuh0bWrMzP5OyNNdydQg6drMRDAib0fcpy7WSfObEZBgbEVpEflb0UQvgop2AVCavEihJWZCVzfBOJZCrbE7ZAri0xXPjPJFPY0o6gqG7nLy7ROxp');
			

			// Get the GraphUser object for the current user:

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
			  $error = "Erreur inconnue";
			}

			$sentence = "Vous avez ".intval($ucount)." notifications non lues.";
			if(isset($error)) $sentence = $error;

			$response = array('responses'=>array(
									array('type'=>'talk','sentence'=>$sentence)
												)
							);
			$json = json_encode($response);
			echo ($json=='[]'?'{}':$json);
		break;
	}

}

function vocalinfo_event(&$response){
	
	if(date('d/m H:i')=='24/01 18:00'){
		if(date('s')<45){
		$response['responses']= array(
								array('type'=>'sound','file'=>'sifflement.wav'),
								array('type'=>'talk','sentence'=>'C\'est l\'anniversaire de mon créateur, pensez à lui offrir une bière!')
							);
		}
	}
}

function vocalinfo_plugin_preference_menu(){
	global $_;
	echo '<li '.(@$_['block']=='vocalinfo'?'class="active"':'').'><a  href="setting.php?section=preference&block=vocalinfo"><i class="fa fa-angle-right"></i> Informations Vocales</a></li>';
}
function vocalinfo_plugin_preference_page(){
	global $myUser,$_,$conf;
	if((isset($_['section']) && $_['section']=='preference' && @$_['block']=='vocalinfo' )  ){
		if($myUser!=false){
	Plugin::addjs("/js/woeid.js",true);
	Plugin::addJs('/js/main.js',true);
	$commands = json_decode(file_get_contents(Plugin::path().'/'.VOCALINFO_COMMAND_FILE),true);

	?>

		<div class="span9 userBloc">
		<legend>Commandes</legend>
	<table class="table table-striped table-bordered">
		<tr>
			<th></th>
			<th>Commande</th>
			<th>Confidence</th>
		</tr>
	<?php	foreach($commands as $key=>$command){ ?>
			<tr class="command" data-id="<?php echo $key; ?>"><td><input type="checkbox" <?php echo $command['disabled']=='true'?'':'checked="checked"' ?> class="enabled"></td><td><?php echo $conf->get('VOCAL_ENTITY_NAME').' '.$command['command']; ?></td><td><input type="text" class="confidence" value="<?php echo $command['confidence']; ?>"/></td></tr>
	<?php	}  ?>
		<tr>
			<td colspan="3"><div class="btn" onclick="plugin_vocalinfo_save();">Enregistrer</div></td>
		</tr>
	</table>
		
		
			<form class="form-inline" action="action.php?action=vocalinfo_plugin_setting" method="POST">
			<legend>Météo</legend>
			    <label>Tapez le nom de votre ville et votre pays</label>
			    <input type="text" class="input-xlarge" name="weather_place" value="<?php echo $conf->get('plugin_vocalinfo_place');?>" placeholder="Votre ville">	
			    <span id="weather_query" class="btn">Chercher</span>
			    <br/><br/><label>Votre Identifiant WOEID</label>
			    <input type="text" class="input-large" name="woeid" value="<?php echo $conf->get('plugin_vocalinfo_woeid');?>" placeholder="Votre WOEID">					
			    <button type="submit" class="btn">Sauvegarder</button>
	    </form>
		</div>

<?php }else{ ?>

		<div id="main" class="wrapper clearfix">
			<article>
					<h3>Vous devez être connecté</h3>
			</article>
		</div>
<?php

		}
	}
}


Plugin::addHook("preference_menu", "vocalinfo_plugin_preference_menu"); 
Plugin::addHook("preference_content", "vocalinfo_plugin_preference_page"); 


Plugin::addHook("get_event", "vocalinfo_event");    
Plugin::addHook("action_post_case", "vocalinfo_action");    
Plugin::addHook("vocal_command", "vocalinfo_vocal_command");
?>

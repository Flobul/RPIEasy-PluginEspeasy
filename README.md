Voici un tuto décrivant l'installation de RPIEasy (adaptation du fameux ESPEasy pour les Raspberry) afin de transformer son Pi en objet multicapteurs.  
Le gros avantage est le port ethernet, on peut se passer de WiFi pour piloter son Pi, à l'instar des ESP sans port ethernet.  
On peut programmer sa connexion : si WiFi down, utilise Ethernet, si Ethernet down, passer en AP. Et inversement...  
La liste des Devices préprogrammés est assez bien fournie.  
Et on peut en créer plusieurs pages...  

Le sujet qui explique le principe : [https://www.letscontrolit.com/forum/viewtopic.php?t=6237](https://www.letscontrolit.com/forum/viewtopic.php?t=6237)  
Le github du projet : [https://github.com/enesbcs/rpieasy](https://github.com/enesbcs/rpieasy)  


Le but de ce tuto étant de simplier l'installation des paquets/soft pour une intégration facile dans Jeedom, via le plugin Espeasy de Lunarok.  


Pré-requis :
- Un Raspberry (Raspberry Pi Zero W/Raspberry 1B/Raspberry 3B+...),  
- un OS installé sur le Pi (Raspbian Buster, Ubuntu... et d'autres dérivés),  
- une console/un terminal permettant de se connecter au Raspberry en SSH.  

# Etape 1 : Se connecter en SSH  

Connectez-vous en SSH au Pi sur lequel vous voulez installer RPIEasy.  
Le mot de passe par défaut du compte SSH sur Raspbian est `raspberry`.  
```
ssh pi@192.168.0.223
```

# Etape 2 : Télécharger et executer le script d'installation  

```
wget https://github.com/Flobul/RPIEasy-PluginEspeasy/raw/master/install_rpieasy.sh
chmod +x install_rpieasy.sh
bash install_rpieasy.sh
```

# Etape 3 : Vérification d'installation  

Si l'installation s'est bien déroulée, le script retourne ceci :  
```
pi@raspberrypi:~ $ bash install_rpieasy.sh
...
RPIEasy installé et lancé
Depuis votre navigateur, saisissez : http://192.168.0.223
```

Sinon, il faudra executer manuellement la commande de lancement et noter l'erreur :  
```
sudo python3.7 RPIEasy.py
```

# Etape 4 : Accéder à l'interface RPIEasy

Ouvrez votre navigateur et saisissez l'adresse de votre Pi dans la barre d'adresse.

Vous tombez sur cette page :
![Accueil](/images/Accueil.png)

# Etape 5 : Configuration de RPIEasy

* La première chose à faire est de configurer le démarrage automatique de RPIEasy.  
  Cliquez sur l'onglet ![Hardware](/images/Hardware.png), cochez `RPIEasy autostart at boot` et cliquer sur Submit.  
  Option : vous pouvez aussi désactiver le port HDMI au démarrage si votre Pi n'a pas d'écran.  
  ![BootON](/images/BootON.png)

* Ensuite, cliquez sur l'onglet ![Controllers](/images/Controllers.png), puis sur Edit, et saisissez les informations suivantes :  
  ![ConfigControllers](/images/ConfigControllers.png)  
    * Protocol:	`Generic HTTP`  
    * Enabled: `Cochez`  
    * Controller Host Address: `l'@IP de votre Jeedom`  
    * Controller Port: `8121`  
    * Report template: `device=%sysname%&taskid=%id%&cmd=%valname%&value=%value%`  
  Cliquez sur Submit.

* On va ajouter quelques informations système :  
    * Cliquez sur l'onglet ![Devices](/images/Devices.png), puis sur Edit, et saisissez les informations suivantes :  
    ![ConfigDevices](/images/ConfigDevices.png)  
    * Name: `le nom que vous voulez`  
    * Enabled: `Cochez`  
    * Indicator1à4: `choisissez les infos que vous voulez remonter`  
    * Send to Controller: `Cochez`  
    * Dans Values, affectez les noms aux valeur récupérées, ce seront les noms qui seront remontés à Jeedom.  
  Cliquez sur Submit.

* Redémarrez le RPi pour appliquer les précédents changements.  

* Pendant ce temps, depuis le plugin Espeasy de Jeedom, lancez une inclusion.  
  Un nouvel équipement devrait être créé :  
  ![Inclusion](/images/Inclusion.png)  

Et voilà !  

# Etape 6 : Créer des commandes action

On peut ajouter des commandes action à Jeedom pour redémarrer, arrêter, mettre à jour le RPiEasy.  
Pour celà, il faut créer des Rules dans le RPIEasy.  
* Cliquez sur l'onglet ![Rules](/images/Rules.png), et saisissez ceci tel quel dans le champ :  

```
on rpiupdate do // rpiupdate receive
  SendToHTTP %ip%,80,/update?mode=rpi
endon
on aptupdate do // aptupdate receive
  SendToHTTP %ip%,80,/update?mode=apt
endon
on pipupdate do // pipupdate receive
  SendToHTTP %ip%,80,/update?mode=pip
endon
on reboot do // reboot receive
  reboot
endon
on halt do // halt receive
  SendToHTTP %ip%,80,/?cmd=halt
endon
```
* Ensuite sur l'équipement dans le plugin Espeasy de Jeedom, créez des commandes comme ceci :  
![CommandesRPIEasy](/images/CommandesRPIEasy.png)  
Puis enregistrez.  

# Etape 7 : Installer les dépendances nécessaires en fonction de l'utilisation que vous en faites  

Dans l'onglet ![Hardware](/images/Hardware.png), cliquez sur *Plugin&controller dependencies*, puis sur la dépendance dont vous avez besoin (GPIO, I2C...).  

# Etape 8 : Brancher les capteurs et autres devices  

Ensuite, c'est à vous de jouer en branchant et configurant vos capteurs et/ou autres dispositifs sur les GPIO.  
Et pensez à effectuer des sauvegardes de la config assez régulièrement.  

# Etape bonus : Surveiller son ESPEasy via Jeedom  

Suite au problème de perte de WiFi, un reboot peut être nécessaire.  
Voir le sujet : [https://community.jeedom.com/t/maintenir-les-esp-en-ligne/24485](https://community.jeedom.com/t/maintenir-les-esp-en-ligne/24485)  
Le but de la manoeuvre est d'envoyer un ping vers le script sur Jeedom et le script renvoie un pong vers RPIeasy.  

Voilà le script de la page PHP à insérer :
```
<?php
// (c) Lenif 2020
// Ajout RPIEasy by Flobul
// Keepalive procedure pour espeasy. Si network hors ligne pour 120 sec, espeasy reboot
// 
// utilisation: placer ce script sur un serveur php ou dans un script jeedom. 
//        http://ipserver/espeasy.php?mode=view      pour afficher les espeasy
//        http://ipserver/espeasy.php?mode=clear     pour effacer les espeasy 
//        http://ipserver/espeasy.php?mode=update    pour mettre à jour les espeasy 
//        http://ipserver/espeasy.php                pour afficher l'ip du client
//  depuis un script dans jeedom: 
//                  http://ipdejeedom/plugins/script/core/ressources/espeasy.php?mode=view
//         
//
// modification dans votre espeasy
// ajouter ceci dans les rules:
//
/*
on system#boot do
  timerset,7,60
endon
on Rules#Timer=7 do // ping
  SendToHTTP ip_de_jeedom,80,/plugins/script/core/ressources/espeasy.php?mode=update
  timerset,7,60
endon
on Pong do // pong receive
  timerset,8,130
endon
on Rules#Timer=8 do // no pong, reboot
  reboot
endon
*/
  $space = '  *  ';
  $page = strtok("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", "?");
  $savefile = "espList.json"; 
  $sec = "10";
  $Pong = '/control?cmd=event,Pong';
  $mode = $_GET["mode"];
  $espList = json_decode(file_get_contents($savefile), true);
  $espIP = $_SERVER['REMOTE_ADDR'];
  $espDateTime = date('d-m-Y H:i:s');
  
  if ($mode == 'update'){
    echo 'Send back pong to:</br>';
    //send pong
    $PongURL = 'http://'.$espIP.$Pong;
    $cURL = curl_init($PongURL); //Initialise cURL 
    curl_setopt($cURL, CURLOPT_PORT, 80); //Set the port to connect to
    curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true); //Get cURL to return the HTML from
    $PongResult = curl_exec($cURL); //Execute the request and store the result in $HTML
    if (strstr($PongResult, "OK") || strstr($PongResult, "True"))
    {
	  $espPong = 'OK';
    }
    else
    {
	  $espPong = 'NOK';
    }
  }	

  // Get esp infos
  $cURL = curl_init('http://'.$espIP.'/json'); //Initialise cURL 
  curl_setopt($cURL, CURLOPT_PORT, 80); //Set the port to connect to
  curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, 2);
  curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true); //Get cURL to return the HTML from
  $espJSON = json_decode(curl_exec($cURL)); //Execute the request and store the result in $HTML
  if (is_null($espJSON))
  {
    $espName = 'N/A';
    $espBuild = 'N/A';
    $espSSID = 'N/A';
    $espRSSI = 'N/A';
    $espUptime = 'N/A';
  } else {
    $espBuild  = $espJSON->{'System'}->{'Build'};
    if (substr($espBuild, 0, 7) === 'RPIEasy') {
      $espName  = $espJSON->{'System'}->{'Name'};
      $espUnit  = $espJSON->{'System'}->{'Unit'};
      $espName  = $espName."-".$espUnit;
      $espRSSI  = $espJSON->{'WiFi'}->{'RSSI'};
      if ($espRSSI == "-49.20051") {
      	$espSSID = "Wired connection";
        $espRSSI = "-";
      } else {
      	$espSSID = $espJSON->{'WiFi'}->{'SSID'};
        $espRSSI = round($espRSSI).'db';
      }
    } else {
      $espName  = $espJSON->{'WiFi'}->{'Hostname'};
      $espBuild = $espJSON->{'System'}->{'Git Build'};
      $espSSID  = $espJSON->{'WiFi'}->{'SSID'};
      $espRSSI  = $espJSON->{'WiFi'}->{'RSSI'}.'db';
    }
      $minutes  = round($espJSON->{'System'}->{'Uptime'});
      $d = floor ($minutes / 1440);
      $h = floor (($minutes - $d * 1440) / 60);
      if ($h < 10) { $h = '0'.$h; }
      $m = $minutes - ($d * 1440) - ($h * 60);
    if ($m < 10) { $m = '0'.$m; }
    if ($d == 0) { 
      $espUptime = $h.':'.$m;
    } else {
      $espUptime = $d.'j. '.$h.':'.$m;  
    }
//
	  
  }

  if (!is_array($espList))
  {
    $espList =array();
  }

 if (($mode == 'delete') and isset($_GET['ip'])){
	//on supprime l'ip transmise  
	$ipToDelete = str_replace("-",".",$_GET['ip']); 
    echo '<head><meta http-equiv="refresh" content="4;'.$page.'?mode=view"></head>';
    foreach ($espList as $index => $esp) 
	{
      if ($esp[0] == $ipToDelete) {
	    echo "Je supprime: ".$ipToDelete;
	    array_splice($espList, $index, 1);
		$result = file_put_contents($savefile,json_encode($espList));		
		echo "<p>Modification enregistrée</p>";
		exit();	  
	  }
	}
	echo "Pas trouvé !";
  }

  elseif ($mode == 'update')
	  // **************** UPDATE *************
  {
	$replaced = False;
    foreach ($espList as $index => $esp) 
	{
	  if ($esp[0] == $espIP) 
	  { 
		$espList[$index][1] = $espDateTime;
	    $espList[$index][2] = $espName;
	    $espList[$index][3] = $espBuild;
	    $espList[$index][4] = $espSSID;
	    $espList[$index][5] = $espRSSI;
	    $espList[$index][6] = $espUptime;
		$espList[$index][7] = $espPong;
		$replaced = true;
		break;
	  }
	} 

	if (!$replaced){
      array_push($espList, array($espIP, $espDateTime, $espName, $espBuild, $espSSID, $espRSSI, $espUptime, $espPong));	
	} else
	{
	  echo 'Update date/time for '.$espIP.'</br>';
	}
	
	$result = file_put_contents($savefile,json_encode($espList));
  } elseif ($mode == 'clear') 
	  // **************** CLEAR *************
  {
    echo '<head><meta http-equiv="refresh" content="2;'.$page.'?mode=view"></head>';
	if (!unlink($savefile)) {  
      echo ("Erreur lors de l\effacement du fichier ".$savefile);  
    }  
    else {  
      echo ("Fchier supprimé");  
    } 
	unlink("espList.json");  
  } elseif ($mode == 'view') 
	  // **************** VIEW *************
  {
    if ($espList == '')
    {
	  $html = '<p>Vide</p>';	
	} else 
	{
	  //trier
      usort($espList, function ($a, $b)
      {
        $aip = sprintf('%u', ip2long($a[0]));
        $bip = sprintf('%u', ip2long($b[0]));
        return $aip <=> $bip;
      });	  
	  
      // afficher	  
	  $html = '<html>
               <head>';
	  $html .= '<meta http-equiv="refresh" content="10">';
      $html .= '<style>
               body {
	 		     font-family: Verdana, "trebuchet ms", Arial;
	 		   }
			   table {
                 border-collapse: collapse;
                 width: 840px;
                 font-size: 12px;
                 border: 5px solid #656cb2;				
               }
               tr.enhaut {
                 background-color: #9ba3ff;
                 padding: 5px;
                 text-align: center;
               }
               th {
			   	 border-bottom: 5px solid #656cb2;
			  	 border-right: 2px solid #656cb2;
			   }
			   tfoot {
                 border: 5px solid #656cb2;
               }
			   tr.offline {
	             background: #ff0000;
                 transition: 0.3s;
			   }
			   tr.red {
	             background: #f28c8c;
                 transition: 0.3s;
			   }
			   tr.green {
	             background: #8cf296;
                 transition: 0.3s;
	           }
			   tr.orange {
	             background: #f7902d;
                 transition: 0.3s;
	           }
			   tr.yellow {
	             background: #ffe56e;
                 transition: 0.3s; 
	           }
			   tr.enbas {
	             background: #656cb2;
                 color: #ffffff;				
	           }
		 	   td {
                 padding: 4px;
                 text-align: left;
                 border-bottom: 1px solid #ffffff;
			  	 border-right: 2px solid #656cb2;
               }
			   td[colspan="9"] {
                 text-align: center;
				 border-bottom: 5px solid #656cb2;
               }
			   tr.enbas:hover {
                 filter: brightness(100%);
			   }
			   tr.enhaut:hover {
                 filter: brightness(100%);
			   }
			   tr:hover {
                 filter: brightness(75%);
			   }
			   tr.enbas a:link, tr.enbas a:visited {
                 text-decoration: none;
				 color: white;
               }
               tr.enbas a:hover, tr.enbas a:active {
                 text-decoration: underline;
			  	 font-weight: bold;
               }
			   td.lien a:hover, td.lien a:active {
                 text-decoration: underline;
				 font-weight: bold;
               }
			   tbody a:link, tbody a:visited {
                 text-decoration: none;
				 color: black;
               }
               tbody a:hover, tbody a:active {
                 text-decoration: underline;
               }
			  
               </style>
               </head>
               <body>';
 	  $html .= '<table>';
      $html .= '<thead><tr class="enhaut"><th>IP</th>'; //0
	  $html .=     '<th>Date</th>'; // 1
	  $html .=     '<th>Nom</th>'; // 2 
	  $html .=     '<th>Build</th>';// 3
	  $html .=     '<th>SSID</th>'; // 4
	  $html .=     '<th>RSSI</th>'; // 5
	  $html .=     '<th>UpTime</th>'; // 6
	  $html .=     '<th>Pong</th>'; // 7
	  $html .=     '<th>Sup</th></tr></thead><tbody>'; // 8
	  foreach($espList as $rowkey=>$row) 
	  {
		$timeDif = (strtotime($espDateTime)-strtotime($row[1]))/60;
		if ($timeDif > 10) {
		  $html .= '<tr class="offline">';
        } elseif ($row[7] == 'NOK') {
		  $html .= '<tr class="red">';			
		} elseif (preg_replace('/[^0-9]/', '', $row[5]) > 75) {   
		  $html .= '<tr class="yellow">';
		} elseif  (preg_replace('/[^0-9]/', '', $row[6]) < 100) {   
		  $html .= '<tr class="orange">';
		} else {
		  $html .= '<tr class="green">';
		}
        foreach ($row as $cellkey=>$cell) {
          $html .= '<td>';
		  //if ($cellkey == 0){
			$html .= '<a href="http://'.$row[0].'"  target="_blank">'.$cell.'</a>';  
		  //} else {
			//$html .= $cell.'</td>';  
		  //}
        }
        $html .= '<td class="lien" style="text-align: center;">';
		$html .= '<a href="'.$page.'?mode=delete&ip='.$row[0].'">X</a>';
		$html .= '</td></tr>';
      }
      $html .= '</tbody>';
	  $html .= '<tfoot>';
      $html .= '<tr class="offline"><td colspan="9">Hors ligne</td></tr>';	  
      $html .= '<tr class="red"><td colspan="9">PONG pas reçu par l\'ESP</td></tr>';	  
      $html .= '<tr class="yellow"><td colspan="9">Signal wifi faible</td></tr>';	  
      $html .= '<tr class="orange"><td colspan="9">ESP redémarré récemment</td></tr>';	  
      $html .= '<tr class="green"><td colspan="9">ESP en pleine forme</td></tr>';	  
      $html .= '<tr class="enbas"><td colspan="9"><a href="'.$page.'?mode=clear">Supprimer le l\'historique</a></td></tr>';	  
	  $html .= '</tfoot>';		
	  $html .= '</table></body></html>';
	}
	echo $html;

  } else
	  // **************** NO MODE *************
  {
	  echo $espIP;
  }
?>
```

# FAQ :  
- Mon RPI ne se connecte pas en WiFi. Comment faire ?
  ```
  Il se peut que le WiFi soit bloqué. Vérifier son état avec la commande 'rfkill list' en SSH. ('rfkill unblock all' pour débloquer)  
  Dans l'onglet 'Config', une fois qu'il apparaît dans 'Primary network device:' allez dans l'onglet 'Hardware' et cliquez sur 'Scan Wifi networks'	 
  Si les réseaux environnants s'affichent, c'est que tout est bon.  
  Dans l'onglet 'Config', pensez à configurer 'Wifi Settings'.  
  Redémarrez le RPi.  
  ```
- Lors de l'inclusion, dans le plugin Espeasy de Jeedom, plusieurs équipements ont été créé alors que j'en ai qu'un seul.
  ```
  Supprimez les équipements qui n'ont qu'une seule commande info pour ne garder qu'un seul équipement (en général, il y a un équipement qui a toutes les commandes et les autres en ont une seule).  
  ```
- J'ai fait la mise à jour de RPIEasy, je ne retrouve plus mes Devices.
  ```
  Rechargez la configuration précédemment sauvegardée.*  
  Pensez à sauvegarder votre config (Onglet Tools, Settings, Save), l'outil de mise à jour est capricieux.*  
  ```
  
# Remerciements :  
Merci à @enesbsc [https://github.com/enesbcs/rpieasy](https://github.com/enesbcs/rpieasy)  
Merci à @Lenif [https://community.jeedom.com/t/maintenir-les-esp-en-ligne/24485](https://community.jeedom.com/t/maintenir-les-esp-en-ligne/24485)  
Merci à @Lunarok  


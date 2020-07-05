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

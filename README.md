# RPIEasy-PluginEspeasy

Voici un tuto décrivant l'installation de RPIEasy (adaptation du fameux ESPEasy pour les Raspberry) afin de transformer son Pi en objet multicapteurs.  
Le but de ce tuto étant de simplier l'installation des paquets/soft pour une intégration facile dans Jeedom, via le plugin Espeasy de Lunarok.  

Pré-requis :
- Un Raspberry (Raspberry Pi Zero W/Raspberry 1B/Raspberry 3B+...),  
- un OS installé sur le Pi (Raspbian Buster, Ubuntu... et d'autres dérivés),  
- une console/un terminal permettant de se connecter au Raspberry en SSH.  

# Etape 1 : Se connecter en SSH  

Connectez-vous en SSH au Pi sur lequel vous voulez installer RPIEasy.  
Le mot de passe par défaut du compte SSH sur Raspbian est `raspberry`.  
`ssh pi@192.168.0.223`

# Etape 2 : Télécharger et executer le script d'installation  

```
wget https://github.com/Flobul/RPIEasy-PluginEspeasy/raw/master/install_rpieasy.sh
chmod +x install_rpieasy.sh
bash install_rpieasy.sh
```

# Etape 3 : Vérification d'installation  

Si l'installation s'est bien déroulée, le script retourne ceci :  
```
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
    * Dans Value, affectez les noms au valeur récupérées, ce seront les noms qui seront remontés à Jeedom.  
  Cliquez sur Submit.

* Maintenant, depuis le plugin Espeasy de Jeedom, lancez une inclusion.  
  Un nouvel équipement devrait être créé :  
  ![Inclusion](/images/Inclusion.png)

Et voilà ! Il ne reste plus qu'à raccorder ses sondes... au GPIO pour les exploiter depuis l'interface.

# Etape 6 : Créer des commandes action

On peut ajouter des commandes action à Jeedom pour redémarrer, arrêter, mettre à jour le RPiEasy.  
Pour celà, il faut créer des Rules dans le RPIEasy.   
* Cliquez sur l'onglet ![Rules](/images/Rules.png), et saisissez ceci dans le champ :  

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

Dans l'onglet ![Hardware](/images/Hardware.png), cliquez sur 'Plugin&controller dependencies' et cliquez sur la dépendance dont vous avez besoin (GPIO, I2C...)  

# Etape bonus : Surveiller son EXPEasy via Jeedom  
Suite au problème de perte de WiFi, un reboot peut être nécessaire.  
Voir le sujet : https://community.jeedom.com/t/maintenir-les-esp-en-ligne/24485  
Le but de la manoeuvre est d'envoyer un ping vers le script sur Jeedom et le script renvoie un pong vers RPIeasy.  
Etape en attente de validation par Lenif pour l'utilisation de son script...  

# FAQ :  
- Mon RPI ne se connecte pas en WiFi. Comment faire ?  
  *Il se peut que le WiFi soit bloqué. Vérifier son état avec la commande 'rfkill list' en SSH. ('rfkill unblock X' pour débloquer)*  
  *Dans l'onglet 'Config', une fois qu'il apparaît dans 'Primary network device:' allez dans l'onglet 'Hardware' et cliquez sur 'Scan Wifi networks'*	 
  *Si les réseau environnant s'affichent, c'est que tout est bon.*  
  *Dans l'onglet 'Config', pensez à configurer 'Wifi Settings'.*  
  *Redémarrez le RPi.*  
- Lors de l'inclusion, dans le plugin Espeasy de Jeedom, plusieurs équipements ont été créé.  
  *Supprimez les équipements à une seule commande info pour ne garder qu'un seul équipement.*
  
  
# Remerciements :  
Merci à @enesbsc https://github.com/enesbcs/rpieasy  
Merci à @Lenif https://community.jeedom.com/t/maintenir-les-esp-en-ligne/24485  

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

`wget https://github.com/Flobul/RPIEasy-PluginEspeasy/raw/master/install_rpieasy.sh`  
`chmod +x install_rpieasy.sh`  
`bash install_rpieasy.sh`

# Etape 3 : Vérification d'installation  

Si l'installation s'est bien déroulée, le script retourne ceci :  
`RPIEasy installé et lancé`  
`Depuis votre navigateur, saisissez : http://192.168.0.223`

Sinon, il faudra executer manuellement la commande de lancement et noter l'erreur :  
`sudo python3.7 RPIEasy.py`

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


# Etape bonus :
(merci ssfd pour le script)



Remerciements :
Merci à @enesbsc https://github.com/enesbcs/rpieasy
Merci à 

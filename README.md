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
`RPIEasy installé et lancé  

Depuis votre navigateur, saisissez : http://192.168.0.223  `

Sinon, il faudra executer manuellement la commande de lancement et noter l'erreur :  
`sudo python3.7 RPIEasy.py`

# Etape bonus :
(merci ssfd pour le script)



Remerciements :
Merci à @enesbsc https://github.com/enesbcs/rpieasy
Merci à 

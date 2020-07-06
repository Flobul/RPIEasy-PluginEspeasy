#!/bin/bash
# Version 0.11
# Commandes pour installer easeasy

# installe les paquets nécessaires
sudo apt install curl python3-pip screen alsa-utils wireless-tools wpasupplicant zip unzip git -y

# télécharge le git RPIEasy
git clone https://github.com/enesbcs/rpieasy.git

# installe RPIEasy
cd rpieasy
sudo pip3 install jsonpickle

# installe les commandes réseau
sudo apt install net-tools

# lance la session de RPIEasy
screen -d -m bash -c "python3.7 RPIEasy.py"

sleep 5
# récupère le code d'erreur de la page RPIEasy
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" localhost)

# récupère mon ip
MY_IP=$(hostname -I)
if [ -z ${MY_IP+x} ]; then MY_IP=$(ip -o -4 addr list eth0 | awk '{print $4}' | cut -d/ -f1); fi

if [ $HTTP_CODE -eq 200 ];
  then echo -e "RPIEasy installé et lancé\n"
  echo -e "Depuis votre navigateur, saisissez : http://${MY_IP}"
else echo -e "RPIEasy non lancé\n"
  echo -e "Veuillez vérifier en lançant \"python3.7 RPIEasy.py\""
fi

#!/bin/bash

aptitude install asterisk -y

# On va remplacer les confs par defaut (full commentées servant de pseudo tuto comme toutes les confs en ASYS)
# par des trucs fonctionnant

mv /etc/asterisk/sip.conf /etc/asterisk/sip_exemple.conf
mv /etc/asterisk/extensions.conf /etc/asterisk/extensions_exemple.conf
mv /etc/asterisk/manager.conf /etc/asterisk/manager_exemple.conf

cp /tmp_scripts/templates/sip.conf /etc/asterisk/sip.conf
cp /tmp_scripts/templates/extensions.conf /etc/asterisk/extensions.conf	
cp /tmp_scripts/templates/manager.conf /etc/asterisk/manager.conf

chmod --reference=/etc/asterisk/sip_exemple.conf /etc/asterisk/sip.conf
chmod --reference=/etc/asterisk/extensions_exemple.conf /etc/asterisk/extensions.conf
chmod --reference=/etc/asterisk/manager_exemple.conf /etc/asterisk/manager.conf

chown --reference=/etc/asterisk/sip_exemple.conf /etc/asterisk/sip.conf
chown --reference=/etc/asterisk/extensions_exemple.conf /etc/asterisk/extensions.conf
chown --reference=/etc/asterisk/manager_exemple.conf /etc/asterisk/manager.conf
#!/usr/bin/env bash

#disable grub de merde
echo 'set grub-pc/install_devices /dev/sda' | debconf-communicate
sudo apt-get update -y -qq
sudo apt-get upgrade -y -qq
sudo apt-get install -y -qq emacs subversion
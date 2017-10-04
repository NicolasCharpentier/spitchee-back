#!/bin/bash

# Désactiver le grub loader qui se lance à la première init de la VM (une interface genre le BIOS)
echo 'set grub-pc/install_devices /dev/sda' | debconf-communicate 

sudo apt-get update -y -qq
sudo apt-get upgrade -y -qq
sudo apt-get install -y -qq $1

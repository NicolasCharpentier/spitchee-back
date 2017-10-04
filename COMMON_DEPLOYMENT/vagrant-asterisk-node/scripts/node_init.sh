#!/usr/bin/env bash

# ché ap pq mais ca fait des trucs alors que deja fais dans vm
sudo apt-get update
sudo apt-get install -y nodejs
sudo apt-get install -y npm

# sudo npm install -g nodemon

# mkdir needed sinon il voudra remonter plus haut
mkdir -p /spitchee_node/NAMI/node_modules
mkdir -p /spitchee_node/SU/node_modules

sudo npm update /spitchee_node/NAMI
sudo npm update /spitchee_node/SU
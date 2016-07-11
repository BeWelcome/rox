#!/usr/bin/env bash

# Update Repositories
sudo apt-get update

# Install Python dependecies
sudo apt-get install -y python-dev
sudo apt-get install -y build-essential
sudo apt-get install -y libssl-dev
sudo apt-get install -y libffi-dev

# Remove outdated dependencies
sudo apt-get --purge remove python-cffi


# Install Ansible
sudo apt-get install -y python-pip
sudo pip install -U ansible==2.1.0.0
ansible --version

# Setup Ansible for Local Use and Run
cp /vagrant/ansible/inventories/dev /etc/ansible/hosts -f
chmod 666 /etc/ansible/hosts
cat /vagrant/ansible/files/authorized_keys >> /home/vagrant/.ssh/authorized_keys
sudo ansible-playbook /vagrant/ansible/playbook.yml -e hostname=$1 --connection=local

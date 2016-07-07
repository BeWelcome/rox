#!/usr/bin/env bash

# Update Repositories
sudo apt-get update

# Install Ansible
sudo apt-get install -y python-pip
sudo pip install ansible==2.1.0.0
ansible --version

# Setup Ansible for Local Use and Run
cp /vagrant/ansible/inventories/dev /etc/ansible/hosts -f
chmod 666 /etc/ansible/hosts
cat /vagrant/ansible/files/authorized_keys >> /home/vagrant/.ssh/authorized_keys
sudo ansible-playbook /vagrant/ansible/playbook.yml -e hostname=$1 --connection=local

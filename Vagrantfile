# -*- mode: ruby -*-
# vi: set ft=ruby :

<<<<<<< HEAD
# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Every Vagrant virtual environment requires a box to build off of.
  config.vm.box = "centos6.4"

  # The url from where the 'config.vm.box' box will be fetched if it
  # doesn't already exist on the user's system.
  config.vm.box_url = "https://github.com/2creatives/vagrant-centos/releases/download/v0.1.0/centos64-x86_64-20131030.box"

  config.vm.network :forwarded_port, guest: 80, host: 8080


  config.vm.provision "shell", path: "bootstrap.sh"

end
=======
PROJECT_NAME = "mfcs"
API_VERSION = "2"

Vagrant.configure(API_VERSION) do |config|
  config.vm.define PROJECT_NAME, primary: true do |config|
    config.vm.provider :virtualbox do |vb|
      vb.name = PROJECT_NAME
      vb.customize ["modifyvm", :id, "--memory", "1024"]
      vb.customize ["modifyvm", :id, "--ioapic", "on"]
      vb.customize ["modifyvm", :id, "--cpus", "4"]
    end

    config.vm.box = "centos6.4"
    config.vm.box_url = "https://github.com/2creatives/vagrant-centos/releases/download/v0.1.0/centos64-x86_64-20131030.box"

    config.vm.network :forwarded_port, guest: 80, host: 8020

    config.vm.provision "shell", path: "bootstrap.sh"
  end
end
>>>>>>> 2e444b7ea9a96464ce5ad1e20f4fa471d74ee4d6

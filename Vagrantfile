# -*- mode: ruby -*-
# vi: set ft=ruby :

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

		config.vm.network :forwarded_port, guest: 80, host: 8080

		config.vm.provision "shell", path: "bootstrap.sh"
	end

	if Vagrant.has_plugin?("vagrant-cachier")
		# Configure cached packages to be shared between instances of the same base box.
		config.cache.scope = :box # or :machine
	end
end

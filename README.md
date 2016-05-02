iConnect Magento Module
======================

A simple Magento environment provisioner for [Vagrant](http://www.vagrantup.com/).

* Creates a running Magento development environment with a few simple commands.
* Runs on Ubuntu (Trusty 14.04 64 Bit) \w PHP 5.5, MySQL 5.5, Apache 2.2
* Uses [Magento CE 1.9.2.4](http://www.magentocommerce.com/download)

## Getting Started

**Prerequisites**

* Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
* Install [Vagrant](http://www.vagrantup.com/)
* In your project directory, run `vagrant up`

The first time you run this, Vagrant will download the bare Ubuntu box image. This can take a little while as the image is a few-hundred Mb. This is only performed once.

Vagrant will configure the base system.

## Usage

* In your browser, head to `127.0.0.1:8080`
* Magento CMS is accessed at `127.0.0.1:8080/admin`
* User: `admin` Password: `password123123`
* Access the virtual machine directly using `vagrant ssh`
* When you're done `vagrant halt`

[Full Vagrant command documentation](http://docs.vagrantup.com/v2/cli/index.html)
# Raspberry Pi WireGuard VPN Project
#### By: Michael Short (mike@fawkesengineering.com)

This project contains information for creating and configuring a Slackware Linux ARM Based WireGuard VPN implementation with a Web Based Configuration Tool.

It was conceived as a Senior Project Proposal idea for the CIT490 Course offered at BYU-Idaho.

The following content is stored in each directory:

### config
Contains the defaults.tgz file. Which is a set of web pages, utilities, config files, and Factory Defaults for the the VPN device.
It can be applied by executing the following on an already configured system:

cd /
tar xvfz defaults.tgz

### docs
Contains project documentation files such as:
  
* Power Point Presentation Slides
* microSD partition and formatting information used in the project
* list of Operating System packages used to create the base OS
* PDF User Manual to assist with setup and configuration

### htdocs
Contains the web pages, css, JavaScript, Image, and html modules used by the web configuration tool.

### scripts
Contains system level scripts used in the project

* rc.d
	Modified Startup Scripts
	these scripts should be deployed into: /etc/rc.d
	
* sbin
	script to update the system time when system has an internet connection
	this script should be deployed into: /usr/local/sbin
	
* vpn
	scripts used by the web based VPN configuration Tool
	these scripts should be deployed in: /usr/local/vpn/
	
### src
Contains the C source code for the runElevated utility used to elevate privleges and perform admin tasks triggered by the web configuration tool.
Virtual Host Manager
=======================

Introduction
------------
This is a PHP CLI system to manage virtual hosts with Apache 2 (>= 2.4) for Linux. 
The system can help us to create virtual host file into Apache 2 directory 
and create register into hosts file.

Use mode
------------
In project directory:

<strong>To create virtual host</strong><br/>
php generate.php \<virtual host name\> \<project path name\> \[\<ip\> \<port\>\] 

<strong>To remove virtual host</strong><br/>
php remove.php \<virtual host name\> 

<strong>To list hosts file</strong><br/>
php list.php

Virtual Host Manager
=======================

Introduction
------------
This is a system PHP CLI to manage virtual hosts with Apache 2.4 for Linux. 
The system can help us to create virtual host file into Apache 2 directory 
and create register into hosts file.

Use mode
------------
In project directory:

To create virtual host
php generate.php <virtual host name> <project path name> [<ip> <port>] 

To remove virtual host
php remove.php <virtual host name> 

To list hosts file
php list.php

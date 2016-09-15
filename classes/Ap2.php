<?php

namespace classes;

use classes\InterfaceAction;
use classes\InterfaceParams;

class Ap2 implements InterfaceAction, InterfaceParams {

    private $_default;
    private $_path_apache2 = null;
    private $_path_a2sites = array();
    private $_server_name = null;
    private $_document_root = null;
    private $_port = null;
    private $_ext = null;

    public function __construct() {

        $this->_path_apache2 = '/etc/apache2';
        $this->_path_a2sites = array(
            'sites-available' => "{$this->_path_apache2}/sites-available",
            'sites-enabled' => "{$this->_path_apache2}/sites-enabled"
        );
    }

    public function setParams(array $params) {

        if (!array_key_exists("server_name", $params))
            throw new \Exception("--- Error: server name parameter. ---\n");

        if (!array_key_exists("document_root", $params))
            throw new \Exception("--- Error: missing document root parameter. ---\n");

        if (!array_key_exists("port", $params))
            throw new \Exception("--- Error: missing port parameter. ---\n");

        $this->_server_name = $params['server_name'];
        $this->_document_root = $params['document_root'];
        $this->_port = $params['port'];
        $this->_ext = '.conf';
    }

    public function getDefault() {

        $APACHE_LOG_DIR = '${APACHE_LOG_DIR}';

        $this->_default = <<<A
<VirtualHost *:{$this->_port}>

	   ServerAdmin webmaster@localhost
       ServerName {$this->_server_name}
       DocumentRoot {$this->_document_root}
       SetEnv "development"
        
	<Directory {$this->_document_root}/>		
		AllowOverride All
		Order allow,deny
		allow from all
        Options Indexes FollowSymLinks
	</Directory>

	ErrorLog {$APACHE_LOG_DIR}/error.log
	CustomLog {$APACHE_LOG_DIR}/access.log combined

</VirtualHost>
A
        ;

        return $this->_default;
    }

    public function update() {

        $this->writeFile();

        # Enter into apache2 directory.
        system("cd {$this->getSite("sites-available")}");
        
        # Register new file configuration.
        $this->a2ensite();

        # Restart service.
        $this->restart();        
    }

    private function writeFile() {

        $path = $this->getSite("sites-available");
        if (!file_put_contents("{$path}/{$this->_server_name}.conf", $this->getDefault()))
            throw new \Exception("Write file [fail]\n");

        echo "Write file [ok]\n";
    }

    public function serverExist() {

        if (file_exists("{$this->getSite("sites-available")}/{$this->_server_name}.conf")) {
            return true;
        }
    }

    public function createServerBackup() {

        $path = $this->getSite("sites-available");
        system("cp {$path}/{$this->_server_name} {$path}/{$this->_server_name}.conf." . time(), $return);
        if ($return)
            throw new \Exception("Create backup [fail]\n");

        echo "Create backup [ok]\n";
    }

    public function getSite($key) {

        if (!array_key_exists($key, $this->_path_a2sites))
            throw new \Exception("Error: $key not found\n");

        return $this->_path_a2sites[$key];
    }

    private function searchFileServer() {

        exec("ls -l {$this->getSite('sites-available')} | egrep {$this->_server_name}.conf.?+ | awk '{print $9}' ", $available, $return);
        if ($return)
            throw new \Exception("--- Error ocurred when search apache files.\n");

        exec("ls -l {$this->getSite('sites-enabled')} | egrep {$this->_server_name}.conf.?+ | awk '{print $9}' ", $enabled, $return);
        if ($return)
            throw new \Exception("--- Error ocurred when search apache files.\n");

        return array($available, $enabled);
    }

    private function rmFiles($path, $files) {
        foreach ($files as $file) {
            if (!unlink("{$path}/$file"))
                throw new Exception(" --- Error: cannot remove file.\n ---");
        }
    }

    private function rmServer() {

        $tmp = $this->searchFileServer();

        if (sizeof($tmp[0]) > 0) {
            $this->rmFiles($this->getSite("sites-available"), $tmp[0]);
            echo "Remove server in sites-available [ok].\n";
        }
        else {
            echo "No server in sites-available [ok].\n";
        }

        if (sizeof($tmp[1]) > 0) {
            $this->rmFiles($this->getSite("sites-enabled"), $tmp[1]);
            echo "Remove server in sites-enabled [ok].\n";
        }
         else {
            echo "No server in sites-enabled [ok].\n";
        }
        
    }

    private function a2ensite(){
        system("a2ensite {$this->_server_name}.conf", $return);
        if ($return)
            throw new \Exception("Activate server [fail]\n");

        echo "Activate server [ok]\n";        
    }
    
    private function restart() {
        system("service apache2 restart", $return);
        if ($return)
            throw new \Exception("Restart server [fail]\n");
        
        echo "Restart server [ok]\n";
    }

    public function remove($serverName) {

        if (empty($serverName))
            throw new \Exception("--- Error: missing server name param. ---\n");

        $this->_server_name = $serverName;

        $this->rmServer();

        $this->restart();
    }

}

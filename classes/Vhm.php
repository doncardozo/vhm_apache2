<?php

namespace classes;
use classes\Ap2;
use classes\Hosts;

class Vhm {

    private $_conf = array();

    public function __construct(array $config) {
        
        # Config server name
        if(!array_key_exists('server_name', $config))
            throw new \Exception("--- Error: server name not found ---\n");
        
        $this->_conf['server_name'] = $config['server_name'];
        
        # Config document root
        $this->_conf['document_root'] = $config['document_root'];
        
        # Config ip address
        $this->_conf['ip_address'] = $config['ip_address'];
        
        # Config port
        $this->_conf['port'] = $config['port'];
      
    }
    
    public function generate(){

        # Check document root property
        if(!array_key_exists('document_root', $this->_conf))
            throw new \Exception("--- Error: document root not found ---\n");
        
        $ap2 = new Ap2();

        $ap2->setParams(array(            
                'server_name' => $this->_conf['server_name'], 
                'document_root' => $this->_conf['document_root'],
                'port' => $this->_conf['port']
        ));

        if($ap2->serverExist()){
            $ap2->createServerBackup();
        }
        
        $ap2->update(); 
        
        $hosts = new Hosts();

        $hosts->setParams(array(            
                'server_name' => $this->_conf['server_name'], 
                'ip_address' => $this->_conf['ip_address'],
                'port' => $this->_conf['port']
        ));
                
        $hosts->update();
        
        echo "Finish process ok.\n";
    }

    public function remove(){
        
        $ap2 = new Ap2();

        $ap2->remove($this->_conf['server_name']); 
        
        $hosts = new Hosts();
                
        $hosts->remove($this->_conf['server_name']);
        
        echo "Finish process ok.\n";        
    }

}

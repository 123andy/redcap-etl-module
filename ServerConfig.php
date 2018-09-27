<?php

namespace IU\RedCapEtlModule;

require_once __DIR__.'/dependencies/autoload.php';

use phpseclib\Crypt\RSA;
use phpseclib\Net\SCP;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;


class ServerConfig implements \JsonSerializable
{
    const AUTH_METHOD_SSH_KEY  = 0;
    const AUTH_METHOD_PASSWORD = 1;
    
    private $name;
    private $serverAddress; # address of REDCap-ETL server
    private $authMethod;
    private $username;
    private $password;
    private $sshKeyFile;
    private $configDir;
    private $etlCommand;  # full path of command to run on REDCap-ETL server

    public function __construct($name)
    {
        $this->name = $name;
        
        $this->authMethod = self::AUTH_METHOD_SSH_KEY;
    }

    public function set($properties)
    {
        # Add validation!!!!
        
        foreach (get_object_vars($this) as $var => $value) {
            if (array_key_exists($var, $properties)) {
                $this->$var = $properties[$var];
            }
        }
    }
    
    public function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }

    public function fromJson($json)
    {
        if (!empty($json)) {
            $object = json_decode($json);
            foreach (get_object_vars($this) as $var => $value) {
                $this->$var = $object->$var;
            }
        }
    }

    public function toJson()
    {
        $json = json_encode($this);
        return $json;
    }

    /**
     * Run the ETL process for this server configuration.
     */
    public function run()
    {
        if (empty($this->serverAddress)) {
            throw new \Exeption('No server address specified.');
        }
            
        if ($this->authMethod == self::AUTH_METHOD_PASSWORD) {
            $ssh = new SSH2($this->serverAddress);
            $ssh->login($username, $this->password);
        } elseif ($this->authMethod == self::AUTH_METHOD_SSH_KEY) {
            $keyFile = $this->getSshKeyFile();
            $key = new RSA();
            $key->setPassword('');
            $keyFileContents = file_get_contents($keyFile);
            if ($keyFileContents === false) {
                throw new \Exception('SSH key file "'.$keyFile.'" could not be accessed.');
            }
            $key->loadKey($keyFileContents);
            $ssh = new SSH2($this->serverAddress);
            $ssh->login($this->username, $key);
        } else {
            throw new \Exception('Unrecognized authentication method: '
                .$this->authMethod);
        }
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getServerAddress()
    {
        return $this->serverAddress;
    }
        
    public function getAuthMethod()
    {
        return $this->authMethod;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
        
    public function getSshKeyFile()
    {
        return $this->sshKeyFile;
    }
    
    public function getConfigDir()
    {
        return $this->configDir;
    }
        
    public function getEtlCommand()
    {
        return $this->etlCommand;
    }
}

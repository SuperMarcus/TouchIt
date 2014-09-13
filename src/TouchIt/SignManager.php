<?php
namespace TouchIt;

use TouchIt\Provider\Provider;

class SignManager{
    const SIGN_TELEPORT = 0;

    /** @var TouchIt */
    private $plugin;

    /** @var Provider */
    private $provider;
    
    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
    }

    public function close(){
        if($this->provider instanceof Provider){
            $this->provider->save();
        }
    }

    public function getLang($k){
        return $this->plugin->getLang($k);
    }

    public function getConfig(){
        return $this->plugin->getConfig();
    }

    /**
     * @return \pocketmine\Server
     */
    public function getServer(){
        return $this->plugin->getServer();
    }

    /**
     * @param Provider $provider
     */
    public function setProvider(Provider $provider){
        $this->provider = $provider;
    }

    /**
     * @return Provider
     */
    public function getProvider(){
        return $this->provider;
    }
}
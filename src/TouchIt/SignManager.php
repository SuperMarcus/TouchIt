<?php
namespace TouchIt;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
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

    public function update(){
        foreach($this->provider->getAll() as $info){
            switch((int) $info['data']['type']){
                case SignManager::SIGN_TELEPORT:
                    $level = $this->getServer()->getLevelByName($info['position']['level']);
                    if($level instanceof Level){
                        /** @var Sign $tile */
                        $tile = $level->getTile(new Vector3($info['position']['x'], $info['position']['y'], $info['position']['z']));
                        if($tile instanceof Sign){
                            $target = $this->getServer()->getLevelByName($info['data']['target']);
                            if($target instanceof Level){
                                if($target->getName() === $this->getConfig()->get("MainLevel")){
                                    $tile->setText("[".$this->getConfig()->get("title")['teleport']."]", $info['data']['description'], "* * *", $this->getLang("update.mainlevel"));
                                    break;
                                }
                                $tile->setText("[".$this->getConfig()->get("title")['teleport']."]", $info['data']['description'], ($this->getConfig()->get("EnableCount") ? $this->getLang("update.count.message") : ""), (str_replace(["{count}", "{max}"], [min(count($target->getPlayers()), $this->getConfig()->get("MaxPlayers")), $this->getConfig()->get("MaxPlayers")], $this->getLang("update.count.show"))));
                                break;
                            }
                            $tile->setText("[".$this->getConfig()->get("title")['teleport']."]", $info['data']['description'], $this->getLang("update.notload.message"));
                        }
                    }
                    break;
                default:
                    $this->plugin->getLogger()->notice("Sign type ".$info['data']['type']." not found. Is it from a higher version of TouchIt?");
            }
        }
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
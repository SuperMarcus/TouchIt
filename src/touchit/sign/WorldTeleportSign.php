<?php
namespace touchit\sign;

use pocketmine\level\Level;
use pocketmine\Player;
use touchit\SignManager;

class WorldTeleportSign extends TouchItSign{
    const ID = "WorldTeleportSign";

    const TELEPORT_SIGN_TARGET_LEVEL = "TargetLevel";
    const TELEPORT_SIGN_DESCRIPTION = "Description";

    /**
     * @param Player $player
     * @param SignManager $manager
     */
    public function onActive(Player $player, SignManager $manager){
        if($player->hasPermission("touchit.sign.use.world-teleport")){
            if(($level = $manager->getServer()->getLevelByName($this->getTargetLevel())) instanceof Level){
                if(!array_search($this->getTargetLevel(), $manager->getConfig()->get("teleport")['main-level']) and ($manager->getConfig()->get("teleport")['max-players'] > 0) and !$player->hasPermission("touchit.sign.use.world-teleport.force") and (count($level->getPlayers()) >= $manager->getConfig()->get("teleport")['max-players'])){
                    $player->sendTip($manager->getTranslator()->translateString("touchit.event.limit", [$this->getTargetLevel()]));
                }else{
                    $player->sendTip($manager->getTranslator()->translateString("touchit.event.teleport", [$this->getTargetLevel()]));
                    $manager->getConfig()->get("teleport", ['safe-spawn' => true])['safe-spawn'] ? $player->teleport($level->getSafeSpawn()) : $player->teleport($level->getSpawnLocation());
                }
            }else{
                $player->sendTip($manager->getTranslator()->translateString("touchit.event.unavailable", [$this->getTargetLevel()]));
            }
        }else{
            $player->sendTip($manager->getTranslator()->translateString("touchit.event.permission"));
        }
    }

    public function doEdit(Player $player, $args, SignManager $manager){

    }

    /**
     * @param SignManager $manager
     */
    public function doUpdate(SignManager $manager){
        if($this->getTargetLevel() !== null){
            $level = $manager->getServer()->getLevelByName($this->getTargetLevel());
            $format = $manager->getConfig()->get("teleport")["format"];
            if($level instanceof Level){
                $max = array_search($this->getTargetLevel(), $manager->getConfig()->get("teleport")['main-level']) ? $manager->getServer()->getMaxPlayers() : ($manager->getConfig()->get("teleport")["max-players"] > 0 ? $manager->getConfig()->get("teleport")["max-players"] : $manager->getServer()->getMaxPlayers());
                $targetCount = min(count($level->getPlayers()), $max);
                if(($targetCount >= $max) and ($manager->getConfig()->get("teleport")["show-full"])){//Full
                    $this->setText("[".$format["title"]."]",//Level unavailable message
                        "----------",
                        $format["full"]
                    );
                }else{
                    $replacement = ['{cur}', '{max}', '{tar}', '{des}'];
                    $replacementData = [$targetCount, $max, $this->getTargetLevel(), $this->getDescription()];
                    $this->setText("[".$format["title"]."]",
                        ...str_replace($replacement, $replacementData, $format["body"])
                    );
                }
            }else{
                $this->setText("[".$format["title"]."]",//Level unavailable message
                    "----------",
                    $format["unavailable"]
                );
            }
        }
    }

    public function setTargetLevel($level){
        if($level instanceof Level){
            $level = $level->getName();
        }
        $this->setFunctionProperty(WorldTeleportSign::TELEPORT_SIGN_TARGET_LEVEL, trim($level));
    }

    public function setDescription($value){
        $this->setFunctionProperty(WorldTeleportSign::TELEPORT_SIGN_DESCRIPTION, trim($value));
    }

    public function getTargetLevel(){
        return (string) $this->getFunctionProperty(WorldTeleportSign::TELEPORT_SIGN_TARGET_LEVEL);
    }

    public function getDescription(){
        return (string) $this->getFunctionProperty(WorldTeleportSign::TELEPORT_SIGN_DESCRIPTION);
    }
}
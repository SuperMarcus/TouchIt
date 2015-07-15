<?php
namespace touchit\sign;

use pocketmine\nbt\tag\Byte;
use pocketmine\Player;
use pocketmine\Server;
use touchit\SignManager;

class PortalSign extends TouchItSign{
    const PORTAL_SIGN_OPTION = "Option";
    const PORTAL_SIGN_PORTAL_NAME = "Name";
    const PORTAL_SIGN_DESCRIPTION = "Description";

    const OPTION_IS_ARRIVAL_ONLY = 0b0000001;
    const OPTION_IS_DEPARTURE_ONLY = 0b0000010;
    const OPTION_DEFAULT = 0b0000000;

    public function doUpdate(SignManager $manager){
        $options = $manager->getConfig()->get("portal", []) + $manager->getConfig()->get("teleport");//Implement overrides
        $format = $options['format'];
        if($this->isPaired()){
            if(($pair = $this->getPair()) instanceof PortalSign){
                $max = array_search($pair->getLevel()->getName(), $options['main-level']) ? $manager->getServer()->getMaxPlayers() : ($options['max-players'] > 0 ? $options['max-players'] : $manager->getServer()->getMaxPlayers());
                $current = min(count($pair->getLevel()->getPlayers()), $max);
                if(($current >= $max) and $options['show-full']){
                    $this->setText("[".$format["title"]."]",//Level unavailable message
                        "----------",
                        $format["full"]
                    );
                }else{
                    $replacement = ['{cur}', '{max}', '{lev}', '{des}', '{pos}', '{nam}', '{tye}'];
                    $replacementData = [$current, $max, $pair->getLevel()->getName(), $this->getDescription(),
                        $pair->getX()."-".$pair->getY()."-".$pair->getZ(),
                        $this->getPortalName(),
                        $this->isArrivalOnly() ? $manager->getTranslator()->translateString("type.portal.arrival") : ($this->isDepartureOnly() ? $manager->getTranslator()->translateString("type.portal.departure") : $manager->getTranslator()->translateString("type.portal.two-way"))
                    ];
                    $this->setText("[".$format["title"]."]",
                        ...str_replace($replacement, $replacementData, $format['body'])
                    );
                }
            }else{
                $this->setText("[".$format['title']."]", "----------", $format['unavailable']);
            }
        }else{
            $this->setText("[TouchIt]", "----------", $manager->getTranslator()->translateString("update.portal.unpaired"), $this->getPortalName());
        }
    }

    public function onActive(Player $player, SignManager $manager){
        if($player->hasPermission("touchit.sign.use.portal")){
            if($this->isPaired()){
                if(($pair = $this->getPair()) instanceof PortalSign){
                    $options = $manager->getConfig()->get("portal", []) + $manager->getConfig()->get("teleport");
                    $max = array_search($pair->getLevel()->getName(), $options['main-level']) ? $manager->getServer()->getMaxPlayers() : ($options['max-players'] > 0 ? $options['max-players'] : $manager->getServer()->getMaxPlayers());
                    if((count($pair->getLevel()->getPlayers()) >= $max) and !$player->hasPermission("touchit.sign.use.world-teleport.force")){
                        $player->sendTip($manager->getTranslator()->translateString("event.limit", [$pair->getLevel()->getName()]));
                    }else{
                        $player->sendTip($manager->getTranslator()->translateString("event.portal.process", [$this->getPortalName()]));
                        $player->teleport($pair);
                    }
                }else{
                    $player->sendTip($manager->getTranslator()->translateString("event.unavailable"));
                }
            }else{
                $player->sendTip($manager->getTranslator()->translateString("event.no-arrival"));
            }
        }else{
            $player->sendTip($manager->getTranslator()->translateString("event.permission"));
        }
    }

    /**
     * @return PortalSign
     */
    public function getPair(){
        foreach(Server::getInstance()->getLevels() as $level){
            foreach($level->getTiles() as $tile){
                if($tile instanceof PortalSign){
                    if($tile->getPortalName() === $this->getPortalName()){
                        return $tile;
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isPaired(){
        return $this->getPair() instanceof PortalSign;
    }

    /**
     * @return string
     */
    public function getPortalName(){
        return (string) $this->getFunctionProperty(PortalSign::PORTAL_SIGN_PORTAL_NAME);
    }

    /**
     * @return bool
     */
    public function isArrivalOnly(){
        return ($this->getOption() & PortalSign::OPTION_IS_ARRIVAL_ONLY) > 0;
    }

    /**
     * @return bool
     */
    public function isDepartureOnly(){
        return ($this->getOption() & PortalSign::OPTION_IS_DEPARTURE_ONLY) > 0;
    }

    /**
     * @return int
     */
    public function getOption(){
        return $this->getFunctionProperty(PortalSign::PORTAL_SIGN_OPTION, new Byte(PortalSign::PORTAL_SIGN_OPTION, PortalSign::OPTION_DEFAULT))->getValue();
    }

    /**
     * @return string
     */
    public function getDescription(){
        return (string) $this->getFunctionProperty(PortalSign::PORTAL_SIGN_DESCRIPTION);
    }

    /**
     * @param int $value
     */
    public function setOption($value){
        if($value !== $this->getOption()){
            $this->setFunctionProperty(PortalSign::PORTAL_SIGN_OPTION, $value, TouchItSign::PROPERTY_BYTE);
        }
    }

    /**
     * @param string $value
     */
    public function setDescription($value){
        $this->setFunctionProperty(PortalSign::PORTAL_SIGN_DESCRIPTION, $value);
    }

    /**
     * @param string $value
     */
    public function setPortalName($value){
        $this->setFunctionProperty(PortalSign::PORTAL_SIGN_PORTAL_NAME, $value);
    }

    /**
     * @param bool $value
     */
    public function setArrivalOnly($value){
        $this->setOption($value ? ($this->getOption() | PortalSign::OPTION_IS_ARRIVAL_ONLY) : ($this->getOption() & (~PortalSign::OPTION_IS_ARRIVAL_ONLY)));
    }

    /**
     * @param bool $value
     */
    public function setDepartureOnly($value){
        $this->setOption($value ? ($this->getOption() | PortalSign::OPTION_IS_DEPARTURE_ONLY) : ($this->getOption() & (~PortalSign::OPTION_IS_DEPARTURE_ONLY)));
    }
}
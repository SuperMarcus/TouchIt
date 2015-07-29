<?php
namespace touchit\sign;

use pocketmine\level\Level;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\Server;
use touchit\SignManager;

class PortalSign extends TouchItSign{
    const ID = "PortalSign";

    const PORTAL_SIGN_OPTION = "Option";
    const PORTAL_SIGN_PORTAL_NAME = "Name";
    const PORTAL_SIGN_DESCRIPTION = "Description";
    const PORTAL_SIGN_TARGET_POS_CASH = "TargetCash";

    const OPTION_IS_ARRIVAL_ONLY = 0b0000001;
    const OPTION_IS_DEPARTURE_ONLY = 0b0000010;
    const OPTION_DEFAULT = 0b0000000;

    private $loadChunk = false;

    public function doEdit(Player $player, $args, SignManager $manager){

    }

    public function doUpdate(SignManager $manager){
        $options = $manager->getConfig()->get("portal", []) + $manager->getConfig()->get("teleport");//Implement overrides
        $format = $options['format'];

        $this->loadChunk = ($options['automatic-chunk-loading'] === true);

        if($this->isPaired()){
            if(($pair = $this->getPair()) instanceof PortalSign){
                if($options['type-detection']){
                    $this->setArrivalOnly($pair->isDepartureOnly());//Automatically set the type
                    $this->setDepartureOnly($pair->isArrivalOnly());
                }

                if($this->isArrivalOnly() and $this->isDepartureOnly() and $options['repair-dead-sign']){//What the hell is this!?!?
                    $this->setArrivalOnly(false);
                    $this->setDepartureOnly(false);
                    $pair->setArrivalOnly(false);
                    $pair->setDepartureOnly(false);
                }

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
                        $this->isArrivalOnly() ? $manager->getTranslator()->translateString("touchit.type.portal.arrival") : ($this->isDepartureOnly() ? $manager->getTranslator()->translateString("touchit.type.portal.departure") : $manager->getTranslator()->translateString("touchit.type.portal.two-way"))
                    ];
                    $this->setText("[".$format["title"]."]",
                        ...str_replace($replacement, $replacementData, $format['body'])
                    );
                }
            }else{
                $this->setText("[".$format['title']."]", "----------", $format['unavailable']);
            }
        }else{
            $this->setText("[TouchIt]", "----------", $manager->getTranslator()->translateString("touchit.update.portal.unpaired"), $this->getPortalName());
        }
    }

    public function onActive(Player $player, SignManager $manager){
        if($player->hasPermission("touchit.sign.use.portal")){
            if($this->isPaired()){
                if(($pair = $this->getPair()) instanceof PortalSign){
                    if($this->isArrivalOnly() or $pair->isDepartureOnly()){
                        $player->sendTip($manager->getTranslator()->translateString("touchit.event.portal.arrival"));
                        return;
                    }
                    $options = $manager->getConfig()->get("portal", []) + $manager->getConfig()->get("teleport");
                    $max = array_search($pair->getLevel()->getName(), $options['main-level']) ? $manager->getServer()->getMaxPlayers() : ($options['max-players'] > 0 ? $options['max-players'] : $manager->getServer()->getMaxPlayers());
                    if((count($pair->getLevel()->getPlayers()) >= $max) and !$player->hasPermission("touchit.sign.use.world-teleport.force")){
                        $player->sendTip($manager->getTranslator()->translateString("touchit.event.limit", [$pair->getLevel()->getName()]));
                    }else{
                        $player->sendTip($manager->getTranslator()->translateString("touchit.event.teleport", [$this->getPortalName()]));
                        $player->teleport($pair);
                    }
                }else{
                    $player->sendTip($manager->getTranslator()->translateString("touchit.event.unavailable", [$this->getPortalName()]));
                }
            }else{
                $player->sendTip($manager->getTranslator()->translateString("touchit.event.no-arrival"));
            }
        }else{
            $player->sendTip($manager->getTranslator()->translateString("touchit.event.permission"));
        }
    }

    /**
     * @return PortalSign
     */
    public function getPair(){
        $cashedLevel = "";
        if($this->loadChunk){
            $this->getTargetChunkCash($chunkX, $chunkZ, $cashedLevel);

            if($cashedLevel !== ""){//Fix unpaired sign
                if(($cashedLevel = Server::getInstance()->getLevelByName($cashedLevel)) instanceof Level){
                    $cashedLevel->loadChunk($chunkX, $chunkZ);
                }
            }
        }

        foreach(Server::getInstance()->getLevels() as $level){
            foreach($level->getTiles() as $tile){
                if($tile instanceof PortalSign and $tile !== $this){
                    if($tile->getPortalName() === $this->getPortalName()){
                        $this->setTargetChunkCash($tile->getX() >> 4, $tile->getZ() >> 4, $level->getName());
                        return $tile;
                    }
                }
            }
        }

        if($cashedLevel instanceof Level){//Destination sign externally removed
            $this->cleanTargetChunkCash();
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isPaired(){
        return ($this->getPair() instanceof PortalSign) or $this->isTargetCashed();
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
    public function isTargetCashed(){
        return $this->getFunctionProperty(PortalSign::PORTAL_SIGN_TARGET_POS_CASH) !== null;
    }

    /**
     * @param int &$chunkX
     * @param int &$chunkZ
     * @param string &$level
     */
    public function getTargetChunkCash(&$chunkX, &$chunkZ, &$level){
        if($this->isTargetCashed()){
            $compound = $this->getFunctionProperty(PortalSign::PORTAL_SIGN_TARGET_POS_CASH);
            /** @var IntArray $chunk */
            $chunk = $compound->Chunk;
            $chunkX = $chunk->getValue()[0];
            $chunkZ = $chunk->getValue()[1];
            $level = trim((string) $compound->Level);
        }
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

    public function cleanTargetChunkCash(){
        $this->removeFunctionProperty(PortalSign::PORTAL_SIGN_TARGET_POS_CASH);
    }

    /**
     * @param int $chunkX
     * @param int $chunkZ
     * @param string $level
     */
    public function setTargetChunkCash($chunkX, $chunkZ, $level){
        $this->setFunctionProperty(PortalSign::PORTAL_SIGN_TARGET_POS_CASH, [
            "Chunk" => new IntArray("Chunk", [$chunkX, $chunkZ]),
            "Level" => new String("Level", $level)
        ], TouchItSign::PROPERTY_COMPOUND);
    }
}
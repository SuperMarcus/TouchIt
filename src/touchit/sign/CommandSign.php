<?php
namespace touchit\sign;

use pocketmine\nbt\tag\Byte;
use pocketmine\Player;
use touchit\command\OperatorCommandSender;
use touchit\SignManager;

class CommandSign extends TouchItSign{
    const ID = "CommandSign";

    const COMMAND_SIGN_COMMAND = "Command";
    const COMMAND_SIGN_COMMAND_STORE = "CommandStore";
    const COMMAND_SIGN_OPTION = "Option";
    const COMMAND_SIGN_DESCRIPTION = "Description";

    const OPTION_IS_PRELOADED_SIGN = 0b0000001;
    const OPTION_IS_OPERATOR_ONLY = 0b0000010;
    const OPTION_RUN_AS_OPERATOR = 0b0000100;
    const OPTION_DEFAULT = 0b0000000;

    public function doUpdate(SignManager $manager){
        $format = $manager->getConfig()->get("command")['format'];
        $this->setText(
            "[".$format['title']."]",
            ...str_replace([
                '{cmd}', '{des}', '{nam}'
            ], [
                $this->getCommand(),
                $this->getDescription(),
                $this->getCommandStore()
            ], $format['body'])
        );
    }

    public function onActive(Player $player, SignManager $manager){
        if($player->hasPermission("touchit.sign.use.command")){
            if($manager->getConfig()->get("command")['notice']){
                $player->sendTip($manager->getTranslator()->translateString("event.command.run"));
            }
            $sender = $this->isRunAsOperator() ? new OperatorCommandSender($player, $manager->getServer()) : $player;
            if($this->isPreloaded()){
                foreach($manager->getPreloadedCommands($this->getCommandStore()) as $cmd){
                    $manager->getServer()->dispatchCommand($sender, str_replace([
                        "@time", "@player", "@display_name", "@level"
                    ], [
                        time(), $player->getName(), $player->getDisplayName(), $player->getLevel()->getName()
                    ], $cmd));
                }
            }else{
                $manager->getServer()->dispatchCommand($sender, str_replace([
                    "@time", "@player", "@display_name", "@level"
                ], [
                    time(), $player->getName(), $player->getDisplayName(), $player->getLevel()->getName()
                ], $this->getCommand()));
            }
        }else{
            $player->sendTip($manager->getTranslator()->translateString("event.permission"));
        }
    }

    /**
     * @return string
     */
    public function getDescription(){
        return (string) $this->getFunctionProperty(CommandSign::COMMAND_SIGN_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getCommand(){
        return (string) $this->getFunctionProperty(CommandSign::COMMAND_SIGN_COMMAND);
    }

    /**
     * @return string
     */
    public function getCommandStore(){
        return (string) $this->getFunctionProperty(CommandSign::COMMAND_SIGN_COMMAND_STORE);
    }

    /**
     * @return bool
     */
    public function isRunAsOperator(){
        return ($this->getOption() & CommandSign::OPTION_RUN_AS_OPERATOR) > 0;
    }

    /**
     * @return bool
     */
    public function isOperatorOnly(){
        return ($this->getOption() & CommandSign::OPTION_IS_OPERATOR_ONLY) > 0;
    }

    /**
     * @return bool
     */
    public function isPreloaded(){
        return ($this->getOption() & CommandSign::OPTION_IS_PRELOADED_SIGN) > 0;
    }

    /**
     * @param string $value
     */
    public function setDescription($value){
        $this->setFunctionProperty(CommandSign::COMMAND_SIGN_DESCRIPTION, trim($value));
    }

    /**
     * @param string $value
     */
    public function setCommand($value){
        $this->setFunctionProperty(CommandSign::COMMAND_SIGN_COMMAND, $value);
    }

    /**
     * @param string $value
     */
    public function setCommandStore($value){
        $this->setFunctionProperty(CommandSign::COMMAND_SIGN_COMMAND_STORE, $value);
    }

    /**
     * @param bool $value
     */
    public function setRunAsOperator($value){
        $this->setOption($value ? ($this->getOption() | CommandSign::OPTION_RUN_AS_OPERATOR) : ($this->getOption() & (~CommandSign::OPTION_RUN_AS_OPERATOR)));
    }

    /**
     * @param bool $value
     */
    public function setOperatorOnly($value){
        $this->setOption($value ? ($this->getOption() | CommandSign::OPTION_IS_OPERATOR_ONLY) : ($this->getOption() & (~CommandSign::OPTION_IS_OPERATOR_ONLY)));
    }

    /**
     * @param bool $value
     */
    public function setPreloaded($value){
        $this->setOption($value ? ($this->getOption() | CommandSign::OPTION_IS_PRELOADED_SIGN) : ($this->getOption() & (~CommandSign::OPTION_IS_PRELOADED_SIGN)));
    }

    /**
     * @param $value
     */
    public function setOption($value){
        $this->setFunctionProperty(CommandSign::COMMAND_SIGN_OPTION, $value, TouchItSign::PROPERTY_BYTE);
    }

    /**
     * @return int
     */
    public function getOption(){
        return $this->getFunctionProperty(CommandSign::COMMAND_SIGN_OPTION, new Byte(CommandSign::COMMAND_SIGN_OPTION, CommandSign::OPTION_DEFAULT))->getValue();
    }
}
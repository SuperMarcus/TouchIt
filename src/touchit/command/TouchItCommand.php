<?php
namespace touchit\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use touchit\listener\PlayerTouchListener;
use touchit\SignManager;

class TouchItCommand extends Command{
    /** @var SignManager */
    private $manager;

    /** @var PlayerTouchListener */
    private $touchListener;

    public function __construct(SignManager $manager, PlayerTouchListener $touchListener){
        $this->manager = $manager;
        $this->touchListener = $touchListener;
        $this->setPermission("touchit.command");
        parent::__construct("touchit", "TouchIt commands", "/touchit <edit> [help|args]", ["touch-it"]);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args){
        if(count($args) > 0){
            switch(strtolower(trim(array_shift($args)))){
                case "edit":
                    if($sender instanceof Player){
                        if($sender->hasPermission("touchit.command.edit")){
                            $this->touchListener->addEditModePlayer($sender, $args);
                            $sender->sendMessage($this->manager->getTranslator()->translateString("touchit.command.edit"));
                        }else{
                            $sender->sendMessage($this->manager->getTranslator()->translateString("touchit.command.permission", [$commandLabel, "edit"]));
                        }
                    }else{
                        $sender->sendMessage($this->manager->getTranslator()->translateString("touchit.command.player_only"));
                    }
                    break;
            }
            return true;
        }
        return false;
    }
}
<?php
namespace TouchIt\Listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use TouchIt\SignManager;

class SignCreateListener implements Listener{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    public function onSignChange(SignChangeEvent $event){
        if(trim(strtolower($event->getLine(0))) === "touchit"){
            if((trim($event->getLine(0)) !== "") and (trim($event->getLine(1)) !== "")){
                $type = -1;
                if(trim($event->getLine(3)) === "" or
                    strtolower(trim($event->getLine(3))) === "teleport" or
                    strtolower(trim($event->getLine(3))) === "t" or
                    strtolower(trim($event->getLine(3))) === "world" or
                    strtolower(trim($event->getLine(3))) === "w"){
                    $type = SignManager::SIGN_TELEPORT;
                    if($this->manager->getConfig()->get("CreateCheck")){
                        if(!$this->manager->getServer()->isLevelLoaded(trim($event->getLine(1)))){
                            $event->setLine(0, "-TouchIt-");
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getLang("create.level.message"));
                            $event->setLine(3, "-TouchIt-");
                            $event->getPlayer()->sendMessage($this->manager->getLang("event.create.invlevel"));
                            return;
                        }
                    }
                    $this->manager->getProvider()->create([
                        "type" => SignManager::SIGN_TELEPORT,
                        "target" => trim($event->getLine(1)),
                        "description" => (trim($event->getLine(2)) === "" ? "To ".trim($event->getLine(1)) : trim($event->getLine(2)))
                    ], $event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                    $event->setLine(0, $this->manager->getLang("create.waitting"));
                    $event->setLine(1, "----------");
                    $event->setLine(2, $this->manager->getLang("create.message"));
                    $event->setLine(3, "-TouchIt-");
                    $event->getPlayer()->sendMessage($this->manager->getLang("event.create.load"));
                    return;
                }
            }
            $event->setLine(0, $this->manager->getLang("create.warning"));
            $event->setLine(1, "----------");
            $event->setLine(2, $this->manager->getLang("create.args.message"));
            $event->setLine(3, "-TouchIt-");
            $event->getPlayer()->sendMessage($this->manager->getLang("event.create.invargs"));
        }
    }
}
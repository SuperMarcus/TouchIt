<?php
namespace touchit\task;

use pocketmine\scheduler\PluginTask;
use touchit\provider\update\OldProviderUpdater;
use touchit\SignManager;
use touchit\TouchIt;

class ProviderUpdaterTask extends PluginTask{
    /** @var OldProviderUpdater */
    private $updater;

    private $done = false;

    /** @var \pocketmine\lang\BaseLang */
    private $translator;

    /** @var SignManager */
    private $manager;

    public function __construct(TouchIt $plugin, OldProviderUpdater $updater){
        parent::__construct($plugin);
        $this->updater = $updater;
        $this->translator = $plugin->getTranslator();
        $this->manager = $plugin->getManager();
    }

    public function onRun($currentTick){
        if($this->updater->doUpdate($this->getOwner()->getServer(), $this->manager)){
            $this->done = true;
            $this->getOwner()->getServer()->getScheduler()->cancelTask($this->getTaskId());
        }
    }

    public function onCancel(){
        if($this->done){
            $this->getOwner()->getLogger()->info($this->translator->translateString("provider.update.done"));
        }
    }
}
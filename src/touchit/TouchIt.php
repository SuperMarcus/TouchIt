<?php
namespace touchit;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use touchit\command\TouchItCommand;
use touchit\listener\PlayerTouchListener;
use touchit\listener\SignCreateListener;
use touchit\listener\SignDestroyListener;
use touchit\provider\Provider;
use touchit\provider\update\OldProviderUpdater;
use touchit\sign\CommandSign;
use touchit\sign\WorldTeleportSign;
use touchit\task\ProviderUpdaterTask;
use touchit\task\SignUpdateTask;

class TouchIt extends PluginBase{
    /** @var BaseLang */
    private $lang;

    /** @var SignManager */
    private $manager;

    /**
     * Call when enable
     */
    public function onEnable(){
        @$this->saveDefaultConfig();
        @$this->reloadConfig();

        Tile::registerTile(WorldTeleportSign::class);
        Tile::registerTile(CommandSign::class);

        $this->lang = new BaseLang($this->getServer()->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE), $this->getFile()."resources/language/");

        $this->manager = new SignManager($this);

        //Update old sign data
        if($this->getConfig()->exists("Provider") and (class_exists(($class = "touchit\\provider\\".$this->getConfig()->get("Provider")))) and is_a($class, Provider::class, true) and !((new \ReflectionClass($class))->isAbstract())){
            $this->getLogger()->info($this->getTranslator()->translateString("provider.update", [(new \ReflectionClass($class))->getShortName()]));
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new ProviderUpdaterTask($this, new OldProviderUpdater(new $class($this))), 20 * 60);
        }

        $this->getServer()->getPluginManager()->registerEvents(new PlayerTouchListener($this->manager), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignCreateListener($this->manager), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignDestroyListener($this->manager), $this);
        $this->getServer()->getCommandMap()->register("touchit", new TouchItCommand($this->manager));

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SignUpdateTask($this), 20 * 5);
    }

    /**
     * @return SignManager
     */
    public function getManager(){
        return $this->manager;
    }

    /**
     * Call when disable
     */
    public function onDisable(){
        $this->manager->close();
    }

    public function getTranslator(){
        return $this->lang;
    }

    /**
     * Get the preloaded commands config folder
     * @return string
     */
    public function getPreloadedDataFolder(){
        $dir = $this->getDataFolder()."commands".DIRECTORY_SEPARATOR;
        @mkdir($dir);
        return $dir;
    }
}
<?php
namespace touchit;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\TileEntityDataPacket;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use touchit\task\TileCreateTask;

class SignManager{
    /** Supported types */
    const SIGN_UNKNOWN = 0;//Unknown type
    const SIGN_WORLD_TELEPORT = 1;//World teleport sign (multi-world)
    const SIGN_PORTAL = 2;//Portal sign
    const SIGN_COMMAND = 3;//Command sign

    /** @var TouchIt */
    private $plugin;

    private $suggest_showed = false;

    /**
     * @param $value
     * @return int
     */
    public static function getType($value){
        if(is_int($value))$value = strval($value);
        switch(strtolower(trim($value))){
            /** Type world teleport sign */
            case "w":
            case "world":
            case "worldteleport":
            case "1":
                return self::SIGN_WORLD_TELEPORT;

            /** Type portal sign */
            case "p":
            case "portal":
            case "2":
                return self::SIGN_PORTAL;

            /** Type command sign */
            case "c":
            case "command":
            case "3":
                return self::SIGN_COMMAND;

            /** Unknown sign type */
            default:
                return self::SIGN_UNKNOWN;
        }
    }

    /**
     * Temporary spawn the sign
     * @param Player $player
     * @param Sign $sign
     * @param array $text
     */
    public static function spawnTemporary(Player $player, Sign $sign, array $text){
        if($sign->closed or ($player->getLevel()->getName() !== $sign->getLevel()->getName()))return;

        $nbt = new NBT(NBT::LITTLE_ENDIAN);
        $nbt->setData(new Compound("", [
            new String("Text1", $text[0]),
            new String("Text2", $text[1]),
            new String("Text3", $text[2]),
            new String("Text4", $text[3]),
            new String("id", Tile::SIGN),
            new Int("x", $sign->getFloorX()),
            new Int("y", $sign->getFloorY()),
            new Int("z", $sign->getFloorZ())
        ]));

        $pk = new TileEntityDataPacket;
        $pk->x = $sign->getFloorX();
        $pk->y = $sign->getFloorY();
        $pk->z = $sign->getFloorZ();
        $pk->namedtag = $nbt->write();

        $player->dataPacket($pk);
    }
    
    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
        $this->suggest_showed = 0;
    }

    public function manuallyUpdate(){
        /*$this->update();*/
    }

    /**
     * Save the default preloaded commands configuration
     * @param $name
     */
    public function saveDefaultPreloadedFile($name){
        if(!file_exists($this->getPreloadedDataFolder().$name.".txt")){
            file_put_contents($this->getPreloadedDataFolder().$name.".txt", @stream_get_contents($this->plugin->getResource("preloaded.txt")));
        }
    }

    /**
     * @deprecated
     *
     * @param string $k
     * @return string
     */
    public function getLang($k){
        return $this->getTranslator()->translateString($k);
    }

    /**
     * @return \pocketmine\utils\Config
     */
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
     * @return \pocketmine\lang\BaseLang
     */
    public function getTranslator(){
        return $this->plugin->getTranslator();
    }

    /**
     * @return string
     */
    public function getPreloadedDataFolder(){
        return $this->plugin->getPreloadedDataFolder();
    }

    /**
     * @param array $calls
     * @param ...$args
     */
    public function createTile(array $calls, ...$args){
        $this->getServer()->getScheduler()->scheduleTask(new TileCreateTask($this->plugin, $calls, ...$args));
    }

    /**
     * @param $name
     * @return array
     */
    public function getPreloadedCommands($name){
        $commands = [];
        if(file_exists($this->getPreloadedDataFolder().$name.".txt") and strlen($content = file_get_contents($this->getPreloadedDataFolder().$name.".txt")) > 0){
            foreach(explode("\n", $content) as $cmd){
                $cmd = trim($cmd);
                if((strlen($cmd) > 0) and ($cmd{0} != '#')){
                    $commands[] = $cmd;
                }
            }
        }else{
            @file_put_contents($this->getPreloadedDataFolder().$name.".txt", @stream_get_contents($this->plugin->getResource("preloaded.txt")));
        }
        return $commands;
    }
}
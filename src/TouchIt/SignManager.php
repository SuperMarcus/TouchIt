<?php
namespace TouchIt;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\EntityDataPacket;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use TouchIt\Provider\Provider;

class SignManager{
    /** Supported types */
    const SIGN_UNKNOWN = 0;//Unknown type
    const SIGN_WORLD_TELEPORT = 1;//World teleport sign (multi-world)
    const SIGN_PORTAL = 2;//Portal sign
    const SIGN_COMMAND = 3;//Command sign

    /** @var TouchIt */
    private $plugin;

    /** @var Provider */
    private $provider;

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

        $pk = new EntityDataPacket;
        $pk->x = $sign->getFloorX();
        $pk->y = $sign->getFloorY();
        $pk->z = $sign->getFloorZ();
        $pk->namedtag = $nbt->write();

        $player->dataPacket($pk);
    }
    
    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
    }

    /**
     * Update sign
     */
    public function update(){
        foreach($this->provider->getAll() as $info){
            $level = $this->getServer()->getLevelByName($info['position']['level']);
            if($level instanceof Level){
                $tile = $level->getTile(new Vector3($info['position']['x'], $info['position']['y'], $info['position']['z']));
                if(!($tile instanceof Sign))continue;
            }else continue;

            switch((int) $info['data']['type']){
                case SignManager::SIGN_WORLD_TELEPORT:
                    if(!$this->getServer()->isLevelLoaded($info['data']['data']['target']) or !(($target = $this->getServer()->getLevelByName($info['data']['data']['target'])) instanceof Level)){
                        $tile->setText("[".$this->getConfig()->get("teleport")['title']."]", "----------", $this->getLang("update.sign.closed"));
                        break;
                    }
                    if($this->getConfig()->get("teleport")['EnableCount'] and $this->getConfig()->get("teleport")['ShowFull'] and (count($target->getPlayers()) >= $this->getConfig()->get("teleport")['MaxPlayers'])){
                        $tile->setText("[".$this->getConfig()->get("teleport")['title']."]", "----------", $this->getLang("update.sign.full"));
                        break;
                    }
                    if(@array_search($info['data']['data']['target'], (array) $this->getConfig()->get("teleport")['MainLevel'])){
                        $tile->setText("[".$this->getConfig()->get("teleport")['title']."]", "----------", $this->getLang("update.sign.lobby"));
                        break;
                    }
                    $tile->setText(
                        "[".$this->getConfig()->get("teleport")['title']."]",
                        ($this->getConfig()->get("teleport")['EnableCount'] ? $info['data']['data']['description'] : "----------"),
                        ($this->getConfig()->get("teleport")['EnableCount'] ? $this->getLang("update.sign.count") : $info['data']['data']['description']),
                        ($this->getConfig()->get("teleport")['EnableCount'] ? str_replace([
                            "{count}",
                            "{max}"
                        ], [
                            min(count($target->getPlayers()), $this->getConfig()->get("teleport")['MaxPlayers']),
                            $this->getConfig()->get("teleport")['MaxPlayers']
                        ], $this->getLang("update.sign.count.format")) : "")
                    );
                    break;
                case SignManager::SIGN_PORTAL:
                    $description = str_split($info['data']['data']['description'], 14);
                    if(count($description) > 1)$description[0] .= "-";
                    else $description[1] = "";
                    switch($info['data']['data']['id']){
                        case 0:
                            $tile->setText("[".$this->getConfig()->get("portal")['title']."]", $this->getLang("type.portal.arrival"), $description[0], $description[1]);
                            break;
                        case 1:
                            if($info['position']['level'] === $info['data']['data']['target'][3]){
                                $tile->setText(
                                    "[".$this->getConfig()->get("portal")['title']."]",
                                    $description[0],
                                    $description[1],
                                    $this->getLang("update.sign.count").": ".count($level->getPlayers())
                                );
                                break;
                            }
                            if(!$this->getServer()->isLevelLoaded($info['data']['data']['target'][3]) or !(($target = $this->getServer()->getLevelByName($info['data']['data']['target'][3])) instanceof Level)){
                                $tile->setText("[".$this->getConfig()->get("portal")['title']."]", "----------", $this->getLang("update.sign.closed"));
                                break;
                            }
                            if($this->getConfig()->get("portal")['EnableCount'] and $this->getConfig()->get("teleport")['ShowFull'] and (count($target->getPlayers()) >= $this->getConfig()->get("teleport")['MaxPlayers'])){
                                $tile->setText("[".$this->getConfig()->get("portal")['title']."]", "----------", $this->getLang("update.sign.full"));
                                break;
                            }
                            $tile->setText(
                                "[".$this->getConfig()->get("portal")['title']."]",
                                $description[0],
                                $description[1],
                                ($this->getConfig()->get("portal")['EnableCount'] ? str_replace(
                                    [
                                        "{count}",
                                        "{max}"
                                    ],
                                    [
                                        min(count($target->getPlayers()), $this->getConfig()->get("teleport")['MaxPlayers']),
                                        $this->getConfig()->get("teleport")['MaxPlayers']
                                    ],
                                    $this->getLang("update.sign.count.format")
                                ) : "")
                            );
                            break;
                    }
                    break;
                case SignManager::SIGN_COMMAND:
                    $tile->setText(
                        "[".$this->getConfig()->get("command")['title']."]",
                        "----------",
                        $info['data']['data']['description']
                    );
                    break;
                default:
                    $this->plugin->getLogger()->notice("Sign type ".$info['data']['type']." not found. Is it from a higher version of TouchIt?");
                    $this->plugin->getLogger()->debug("Sign data: ".print_r($info));
            }
        }
    }

    /**
     * Call when disable
     */
    public function close(){
        if($this->provider instanceof Provider){
            $this->provider->save();
        }
    }

    /**
     * @param string $k
     * @return string
     */
    public function getLang($k){
        return $this->plugin->getLang($k);
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
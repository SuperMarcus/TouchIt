<?php
namespace touchit\provider\update;

use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\Server;
use touchit\provider\Provider;
use touchit\sign\WorldTeleportSign;
use touchit\SignManager;

/**
 * Class OldProviderUpdater
 *
 * This class updates old version data to new one which there's no 'provider' actually exists in the new version of touchit
 * All the data will be store with level
 * This class is also temporarily used and should be removed soon
 *
 * @package touchit\provider\update
 */
class OldProviderUpdater{
    /** Old types */
    const SIGN_UNKNOWN = 0;//Unknown type
    const SIGN_WORLD_TELEPORT = 1;//World teleport sign (multi-world)
    const SIGN_PORTAL = 2;//Portal sign
    const SIGN_COMMAND = 3;//Command sign

    /** @var Provider */
    private $provider;

    public function __construct(Provider $provider){
        $this->provider = $provider;
    }

    /**
     * @param Server $server
     * @param SignManager $manager
     * @return bool
     */
    public function doUpdate(Server $server, SignManager $manager){
        $signs = $this->getProvider()->getAll();
        if(count($signs) > 0){
            foreach($signs as $sign){
                $data = $sign['data'];
                switch((int) $data['type']){
                    case OldProviderUpdater::SIGN_UNKNOWN:
                        $this->getProvider()->remove($sign['position']['x'], $sign['position']['y'], $sign['position']['z'], $sign['position']['level']);
                        break;
                    case OldProviderUpdater::SIGN_WORLD_TELEPORT:
                        if(($level = $server->getLevelByName($sign['position']['level'])) instanceof Level){
                            $this->getProvider()->remove($sign['position']['x'], $sign['position']['y'], $sign['position']['z'], $sign['position']['level']);
                            $manager->createTile([
                                    ["setDescription", [$data['data']['description']]],
                                    ["setTargetLevel", [$data['data']['target']]],
                                ],
                                WorldTeleportSign::ID,
                                $level->getChunk($sign['position']['x'] >> 4, $sign['position']['z'] >> 4),
                                new Compound("", [
                                    "id" => new String("id", WorldTeleportSign::ID),
                                    "x" => new Int("x", $sign['position']['x']),
                                    "y" => new Int("y", $sign['position']['y']),
                                    "z" => new Int("z", $sign['position']['z']),
                                    "Text1" => new String("Text1", ""),
                                    "Text2" => new String("Text2", ""),
                                    "Text3" => new String("Text3", ""),
                                    "Text4" => new String("Text4", "")
                                ]));
                        }
                        break;
                    default:
                        $this->getProvider()->remove($sign['position']['x'], $sign['position']['y'], $sign['position']['z'], $sign['position']['level']);
                }
            }
            return false;
        }
        return true;
    }

    /**
     * @return Provider
     */
    private function getProvider(){
        return $this->provider;
    }
}
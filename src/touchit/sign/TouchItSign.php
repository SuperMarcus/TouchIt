<?php
namespace touchit\sign;

use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Tag;
use pocketmine\Player;
use pocketmine\tile\Sign;
use touchit\SignManager;

/**
 * Base of all function signs
 *
 * @package touchit\sign
 */
abstract class TouchItSign extends Sign{
    const PROPERTY_STRING = String::class;
    const PROPERTY_INTEGER = Int::class;
    const PROPERTY_BYTE = Byte::class;
    const PROPERTY_COMPOUND = Compound::class;

    /**
     * Method to call when update the sign
     *
     * @param SignManager $manager
     */
    abstract public function doUpdate(SignManager $manager);

    /**
     * Method to call when active the sign
     *
     * @param Player $player
     * @param SignManager $manager
     */
    abstract public function onActive(Player $player, SignManager $manager);

    /**
     * Method to call to apply a edit command line
     *
     * @param Player $player
     * @param string[] $args
     * @param SignManager $manager
     */
    abstract public function doEdit(Player $player, $args, SignManager $manager);

    public function __construct(FullChunk $chunk, Compound $nbt){
        if(!isset($nbt->FunctionSignData)){
            $nbt->FunctionSignData = new Compound("FunctionSignData", []);
        }

        $nbt->Text1 = new String("Text1", "[TouchIt]");
        $nbt->Text2 = new String("Text2", "----------");
        $nbt->Text3 = new String("Text3", "Loading...");
        $nbt->Text4 = new String("Text4", "");

        parent::__construct($chunk, $nbt);
    }

    /**
     * @return Compound
     */
    protected function getFunctionPropertiesCompound(){
        return $this->namedtag->FunctionSignData;
    }

    /**
     * @param string $key
     * @param Tag|null $default
     * @return Tag
     */
    protected function getFunctionProperty($key, Tag $default = null){
        return isset($this->getFunctionPropertiesCompound()->{$key}) ? $this->getFunctionPropertiesCompound()->{$key} : $default;
    }

    /**
     * @param string $key
     * @param $value
     * @param $tag
     */
    protected function setFunctionProperty($key, $value, $tag = TouchItSign::PROPERTY_STRING){
        $this->getFunctionPropertiesCompound()->{$key} = new $tag($key, $value);
    }

    /**
     * @param $key
     */
    protected function removeFunctionProperty($key){
        unset($this->getFunctionPropertiesCompound()->{$key});
    }
}
<?php
namespace TouchIt\Command;

use pocketmine\command\CommandSender;
use pocketmine\permission\PermissibleBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\permission\PermissionAttachment;

class OperatorCommandSender implements CommandSender{
    /** @var Player */
    private $sender;

    /** @var Server */
    private $server;

    /** @var PermissibleBase */
    private $perm;

    public function __construct(Player $sender, Server $server){
        $this->sender = $sender;
        $this->server = $server;
        $this->perm = new PermissibleBase($this);
    }

    /**
     * @param \pocketmine\permission\Permission|string $name
     * @return bool
     */
    public function isPermissionSet($name){
        return $this->perm->isPermissionSet($name);
    }

    /**
     * @param \pocketmine\permission\Permission|string $name
     * @return bool|mixed
     */
    public function hasPermission($name){
        return $this->perm->hasPermission($name);
    }

    /**
     * @param Plugin $plugin
     * @param null $name
     * @param null $value
     * @return PermissionAttachment
     * @throws \Exception
     */
    public function addAttachment(Plugin $plugin, $name = null, $value = null){
        return $this->perm->addAttachment($plugin, $name, $value);
    }

    /**
     * @param PermissionAttachment $attachment
     * @throws \Exception
     */
    public function removeAttachment(PermissionAttachment $attachment){
        $this->perm->removeAttachment($attachment);
    }

    public function recalculatePermissions(){
        $this->perm->recalculatePermissions();
    }

    /**
     * @return \pocketmine\permission\Permission[]|\pocketmine\permission\PermissionAttachmentInfo[]
     */
    public function getEffectivePermissions(){
        return $this->perm->getEffectivePermissions();
    }

    /**
     * @return bool
     */
    public function isPlayer(){
        return false;
    }

    /**
     * @return Server
     */
    public function getServer(){
        return $this->server;
    }

    /**
     * @param string $message
     */
    public function sendMessage($message){
        foreach(explode("\n", trim($message)) as $line){
            $this->sender->sendMessage($line);
        }
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->sender->getName();
    }

    /**
     * @return bool
     */
    public function isOp(){
        return true;
    }

    /**
     * @param bool $value
     */
    public function setOp($value){
        $this->sender->setOp($value);
    }
}
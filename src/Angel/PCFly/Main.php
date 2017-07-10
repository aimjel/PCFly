<?php

namespace Angel\PCFly;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Main extends PluginBase implements Listener{

    public $fly = [];
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("PC Fly made by Angel(@VortexZMcPe)");
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(strtolower($command->getName()) == "fly"){
            if($sender->hasPermission("fly.command") || $sender->isOp()){
                if(isset($this->fly[strtolower($sender->getName())])){
                    unset($this->fly[strtolower($sender->getName())]);
                    $sender->setAllowFlight(false);
                    $sender->setGamemode(1); $sender->setGamemode(0);
                    $sender->sendMessage(TF::RED."Fly disabled!");
                } else {
                    $this->fly[strtolower($sender->getName())] = strtolower($sender->getName());
                    $sender->setAllowFlight(true);
                    $sender->sendMessage(TF::GREEN."Fly enabled!");
                }
            } else {
                $sender->sendMessage(TF::RED."You dont have permission to use this command");
            }
        }
    }

    public function onHits(EntityDamageEvent $ev){
        if($ev instanceof EntityDamageByEntityEvent){
            $p = $ev->getEntity();
            $damager = $ev->getDamager();
            if($damager instanceof Player && $p instanceof Player){
                if(isset($this->fly[strtolower($damager->getName())])){
                    $damager->sendTip(TF::RED."Flight disabled!");
                    $damager->setGamemode(1); $damager->setGamemode(0);
                }
            }
        }
        if(($p = $ev->getEntity()) instanceof Player){
            if(isset($this->fly[strtolower($p->getName())])){
                $p->sendTip(TF::RED."Flight disabled");
                $p->setGamemode(1); $p->setGamemode(0);
            }
        }
    }
}

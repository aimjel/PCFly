<?php

declare(strict_types=1);

namespace Angel\PCFly;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

/**
 * Class Main
 * @package Angel\PCFly
 */
class Main extends PluginBase implements Listener{

    /** @var Config */
    private $cfg;

    public function onEnable() : void{
        $this->getLogger()->info(TextFormat::GREEN . "PCFly has been enabled!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $folder = $this->getDataFolder();
        if(is_dir($folder) == false)
            mkdir($folder);

        $this->cfg = is_file($folder . 'config.yml') ? new Config($folder . 'config.yml') : new Config($folder . 'config.yml', Config::YAML, [
            'fly_command.on' => '&aFly enabled',
            'fly_command.off' => '&cFly disabled!',
            'fly_eventHit_disabled' => '&cNo Fly in PvP!',

        ]);
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if(strtolower($command->getName()) == 'fly'){

            if($sender instanceof Player == false){
                $sender->sendMessage(TextFormat::RED . 'This game is only to be used in-game!');
                return false;
            }

            if($sender->hasPermission('fly.command') == false or $sender->isOp() == false){
                $sender->sendMessage(TextFormat::colorize($this->cfg->get('fly_noPermission')));
                return false;
            }

            $sender->setAllowFlight($sender->getAllowFlight() == false ? true : false);
            $sender->setFlying($sender->getAllowFlight() == false ? true : false);
            $table = [true => 'on', false => 'off'];
            $sender->sendMessage(TextFormat::colorize($this->cfg->get('fly_command.' . $table[$sender->getAllowFlight()])));
            return true;
        }
        return true;
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onDamage(EntityDamageEvent $event) : void{
        $entity = $event->getEntity();
        if($event->getCause() !== $event::CAUSE_FALL){
            if($entity instanceof Player){
                if($entity->getAllowFlight() == true){
                    $entity->setFlying(false);
                    $entity->setAllowFlight(false);
                    $entity->sendMessage(TextFormat::colorize($this->cfg->get('fly_eventHit_disabled')));
                }
            }
        }
    }

    /**
     * @param EntityLevelChangeEvent $event
     */
    public function onLevelChange(EntityLevelChangeEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if($entity->getAllowFlight() == true){
                $entity->setFlying(false);
                $entity->setAllowFlight(false);
            }
        }
    }
}

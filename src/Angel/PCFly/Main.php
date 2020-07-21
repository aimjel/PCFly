<?php


namespace Angel\PCFly;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Mainn
 * @package Angel\PCFly
 */
class Main extends PluginBase implements Listener {

    /** @var boolean */
    private $pvp;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveConfig();
        $this->pvp = $this->getConfig()->get("disable-fly-on-pvp", true);
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();

        if ($player->isSurvival(true)) {
            $player->setAllowFlight($player->hasPermission("fly.command"));
        }
   }

    /**
     * @param PlayerToggleFlightEvent $event
     */
    public function onToggleFlight(PlayerToggleFlightEvent $event) : void{
        $player = $event->getPlayer();

        if ($player->isSurvival(true)){
            if ($event->isFlying()){
                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LAUNCH);
            }
        }
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if(strtolower($command->getName()) == "fly"){

            if(!($sender instanceof Player)){
                return true;
            }

            if(!$command->testPermission($sender)){
                return true;
            }

            if ($sender->isCreative(true)){
                $sender->sendMessage(TextFormat::DARK_RED."You cannot use /fly in creative");
                return true;
            }

            if (!$sender->getAllowFlight()){
                $sender->setAllowFlight(true);
                $sender->sendMessage(TextFormat::GREEN."Your flight has been enabled");
            } else {
                $sender->setAllowFlight(false);
                $sender->setFlying(false);
                $sender->sendMessage(TextFormat::RED."Your flight has been disabled!");
            }
        }

        return false;
    }

    /**
     * @param EntityLevelChangeEvent $event
     */
    public function onLevelChange(EntityLevelChangeEvent $event) : void{
        $entity = $event->getEntity();
        if ($entity instanceof Player){
            if ($entity->getAllowFlight()){
                $entity->setFlying(false);
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event) : void{
        if (!$this->pvp){
            return;
        }

        if ($event instanceof EntityDamageByEntityEvent){
            $victim = $event->getEntity();
            $attacker = $event->getDamager();

            if($victim instanceof Player and $attacker instanceof Player) {
                foreach ([$victim, $attacker] as $player){
                    if ($player->getAllowFlight()){
                        $player->setAllowFlight(false);
                        $player->setFlying(false);
                    }
                }
            }
        }
    }
}
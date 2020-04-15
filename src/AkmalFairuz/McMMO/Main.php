<?php

/*
 *
 *              _                             _        ______             _
 *     /\      | |                           | |      |  ____|           (_)
 *    /  \     | | __   _ __ ___      __ _   | |      | |__       __ _    _    _ __    _   _    ____
 *   / /\ \    | |/ /  | '_ ` _ \    / _` |  | |      |  __|     / _` |  | |  | '__|  | | | |  |_  /
 *  / ____ \   |   <   | | | | | |  | (_| |  | |      | |       | (_| |  | |  | |     | |_| |   / /
 * /_/    \_\  |_|\_\  |_| |_| |_|   \__,_|  |_|      |_|        \__,_|  |_|  |_|      \__,_|  /___|
 *
 * Discord: akmal#7191
 * GitHub: https://github.com/AkmalFairuz
 *
 */

namespace AkmalFairuz\McMMO;

use AkmalFairuz\McMMO\command\McmmoCommand;
use AkmalFairuz\McMMO\command\McmmoSetupCommand;
use AkmalFairuz\McMMO\entity\FloatingText;
use pocketmine\block\Solid;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{

    public const LUMBERJACK = 0;
    public const FARMER = 1;
    public const MINER = 3;
    public const EXCAVATION = 2;
    public const COMBAT = 5;
    public const KILLER = 4;
    public const BUILDER = 6;
    public const CONSUMER = 7;
    public const ARCHER = 8;
    public const LAWN_MOWER = 9;


    /** @var array */
    public $database;

    /** @var Main */
    public static $instance;

    public function onEnable()
    {
        $this->saveResource("database.yml");
        $this->getServer()->getCommandMap()->register("mcmmo", new McmmoCommand("mcmmo", $this));
        $this->getServer()->getCommandMap()->register("mcmmoadmin", new McmmoSetupCommand("mcmmoadmin", $this));
        $this->database = yaml_parse(file_get_contents($this->getDataFolder() . "database.yml"));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        Entity::registerEntity(FloatingText::class, true);
        self::$instance = $this;
    }

    public static function getInstance() : Main {
        return self::$instance;
    }

    public function onDisable()
    {
        file_put_contents($this->getDataFolder() . "database.yml", yaml_emit($this->database));
        sleep(3); // save database delay
    }

    public function getXp(int $type, Player $player) : int {
        return $this->database["xp"][$type][strtolower($player->getName())];
    }

    public function getLevel(int $type, Player $player) : int {
        return $this->database["level"][$type][strtolower($player->getName())];
    }

    public function addXp(int $type, Player $player) {
        $this->database["xp"][$type][strtolower($player->getName())]++;
        if($this->database["xp"][$type][strtolower($player->getName())] >= ($this->getLevel($type, $player) * 100)) {
            $this->database["xp"][$type][strtolower($player->getName())] = 0;
            $this->addLevel($type, $player);
        }
        $a = ["Lumberjack", "Farmer", "Excavation", "Miner", "Killer", "Combat", "Builder", "Consumer", "Archer", "Lawn Mower"];
        $player->sendTip("Your McMMO ".$a[$type]." xp is ".$this->getXp($type, $player));
    }

    public function addLevel(int $type, Player $player) {
        $this->database["level"][$type][strtolower($player->getName())]++;
        $a = ["Lumberjack", "Farmer", "Excavation", "Miner", "Killer", "Combat", "Builder", "Consumer", "Archer", "Lawn Mower"];
        $player->sendMessage("Your McMMO ".$a[$type]." level is ".$this->getLevel($type, $player));
    }

    public function getAll(int $type) : array {
        return $this->database["level"][$type];
    }

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        if(!isset($this->database["xp"][0][strtolower($player->getName())])) {
            for($i = 0; $i < 10; $i++) {
                $this->database["xp"][$i][strtolower($player->getName())] = 0;
                $this->database["level"][$i][strtolower($player->getName())] = 1;
            }
        }
    }

    /**
     * @priority LOWEST
     */
    public function onBreak(BlockBreakEvent $event) {
        if($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        $block = $event->getBlock();
        switch($block->getId()) {
            case Item::WHEAT_BLOCK:
            case Item::BEETROOT_BLOCK:
            case Item::PUMPKIN_STEM:
            case Item::PUMPKIN:
            case Item::MELON_STEM:
            case Item::MELON_BLOCK:
            case Item::CARROT_BLOCK:
            case Item::POTATO_BLOCK:
            case Item::SUGARCANE_BLOCK:
                $this->addXp(self::FARMER, $player);
                return;
            case Item::STONE:
            case Item::DIAMOND_ORE:
            case Item::GOLD_ORE:
            case Item::REDSTONE_ORE:
            case Item::IRON_ORE:
            case Item::COAL_ORE:
            case Item::EMERALD_ORE:
            case Item::OBSIDIAN:
                $this->addXp(self::MINER, $player);
                return;
            case Item::LOG:
            case Item::LOG2:
            case Item::LEAVES:
            case Item::LEAVES2:
                $this->addXp(self::LUMBERJACK, $player);
                return;
            case Item::DIRT:
            case Item::GRASS:
            case Item::GRASS_PATH:
            case Item::FARMLAND:
            case Item::SAND:
            case Item::GRAVEL:
                $this->addXp(self::EXCAVATION, $player);
                return;
            case Item::TALL_GRASS:
            case Item::YELLOW_FLOWER:
            case Item::RED_FLOWER:
            case Item::CHORUS_FLOWER:
                $this->addXp(self::LAWN_MOWER, $player);
                return;
        }
    }

    /**
     * @priority LOWEST
     */
    public function onPlace(BlockPlaceEvent $event) {
        if($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if($block instanceof Solid) {
            $this->addXp(self::BUILDER, $player);
            return;
        }
    }

    /**
     * @priority LOWEST
     */
    public function onDamage(EntityDamageEvent $event) {
        if($event->isCancelled()) {
            return;
        }
        if($event->getEntity() instanceof FloatingText) {
            $event->setCancelled();
            return;
        }
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if(!$entity instanceof Player) return;
            $damager = $event->getDamager();
            if($damager instanceof Player) {
                if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
                    $this->addXp(self::KILLER, $damager);
                }
                $this->addXp(self::COMBAT, $damager);
            }
        }
    }

    /**
     * @priority LOWEST
     */
    public function onShootBow(EntityShootBowEvent $event) {
        if($event->isCancelled()) {
            return;
        }
        $entity = $event->getEntity();
        if($entity instanceof Player) {
            $this->addXp(self::ARCHER, $entity);
        }
    }

    /**
     * @priority LOWEST
     */
    public function onItemConsume(PlayerItemConsumeEvent $event) {
        if($event->getPlayer()->getFood() < $event->getPlayer()->getMaxFood()) {
            $this->addXp(self::CONSUMER, $event->getPlayer());
        }
    }
}

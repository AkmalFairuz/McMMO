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

namespace AkmalFairuz\McMMO\form;

use AkmalFairuz\McMMO\formapi\FormAPI;
use AkmalFairuz\McMMO\Main;
use pocketmine\Player;

class McmmoForm
{
    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function init(Player $player) {
        $form = (new FormAPI())->createSimpleForm(function (Player $player, $data) {
            if($data === null) {
                return;
            }
            switch($data) {
                case 0:
                    $this->stats($player);
                    return;
                case 1:
                    $this->leaderboard($player);
                    return;
            }
        });
        $form->setTitle("McMMO");
        $form->addButton("Your stats");
        $form->addButton("Leaderboard");
        $form->sendToPlayer($player);
    }

    public function stats(Player $player) {
        $form = (new FormAPI())->createSimpleForm(function (Player $player, $data) {
            if($data !== null) {
                $this->init($player);
            }
        });
        $form->setTitle("Your stats");
        $content = [
            "Lumberjack: ",
            "   XP: ".$this->plugin->getXp(Main::LUMBERJACK, $player),
            "   Level: ".$this->plugin->getLevel(Main::LUMBERJACK, $player),
            "Farmer: ",
            "   XP: ".$this->plugin->getXp(Main::FARMER, $player),
            "   Level: ".$this->plugin->getLevel(Main::FARMER, $player),
            "Excavation: ",
            "   XP: ".$this->plugin->getXp(Main::EXCAVATION, $player),
            "   Level: ".$this->plugin->getLevel(Main::EXCAVATION, $player),
            "Miner: ",
            "   XP: ".$this->plugin->getXp(Main::MINER, $player),
            "   Level: ".$this->plugin->getLevel(Main::MINER, $player),
            "Killer: ",
            "   XP: ".$this->plugin->getXp(Main::KILLER, $player),
            "   Level: ".$this->plugin->getLevel(Main::KILLER, $player),
            "Combat: ",
            "   XP: ".$this->plugin->getXp(Main::COMBAT, $player),
            "   Level: ".$this->plugin->getLevel(Main::COMBAT, $player),
            "Builder: ",
            "   XP: ".$this->plugin->getXp(Main::BUILDER, $player),
            "   Level: ".$this->plugin->getLevel(Main::BUILDER, $player),
            "Consumer: ",
            "   XP: ".$this->plugin->getXp(Main::CONSUMER, $player),
            "   Level: ".$this->plugin->getLevel(Main::CONSUMER, $player),
            "Archer: ",
            "   XP: ".$this->plugin->getXp(Main::ARCHER, $player),
            "   Level: ".$this->plugin->getLevel(Main::ARCHER, $player),
            "Lawn Mower: ",
            "   XP: ".$this->plugin->getXp(Main::LAWN_MOWER, $player),
            "   Level: ".$this->plugin->getLevel(Main::LAWN_MOWER, $player)
        ];
        $form->setContent(implode("\n", $content));
        $form->addButton("Back");
        $form->sendToPlayer($player);
    }

    public function leaderboard(Player $player) {
        $a = ["Lumberjack", "Farmer", "Excavation", "Miner", "Killer", "Combat", "Builder", "Consumer", "Archer", "Lawn Mower"];
        $form = (new FormAPI())->createSimpleForm(function (Player $player, $data) use ($a) {
            if($data === null) {
                return;
            }
            if($data === count($a)) {
                $this->init($player);
                return;
            }
            $this->leaderboards($player, $data);
        });
        $form->setTitle("Leaderboard");
        $form->setContent("");
        foreach($a as $as) {
            $form->addButton($as);
        }
        $form->addButton("Back");
        $form->sendToPlayer($player);
    }

    public function leaderboards(Player $player, int $type) {
        $form = (new FormAPI())->createSimpleForm(function (Player $player, $data) {
            if($data !== null) {
                $this->leaderboard($player);
            }
        });
        $a = ["Lumberjack", "Farmer", "Excavation", "Miner", "Killer", "Combat", "Builder", "Consumer", "Archer", "Lawn Mower"];
        $form->setTitle("Leaderboard ".$a[$type]);
        $content = "";
        $a = $this->plugin->getAll($type);
        arsort($a);
        $i = 1;
        foreach($a as $key => $as) {
            if($i == 20) break;
            $content .= $i.") ".$key . " : ".$as."\n";
            $i++;
        }
        $form->setContent("Player Name | Level\n\n".$content);
        $form->addButton("Back");
        $form->sendToPlayer($player);
    }
}

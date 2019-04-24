<?php

declare(strict_types=1);

namespace Wertzui123\Rewards;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener
{

    public function onEnable(): void
    {
        $this->saveResource("config.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }


    public function onJoin(PlayerJoinEvent $event)
    {
        if (!file_exists($this->getDataFolder() . $event->getPlayer()->getName() . ".yml")) {
            $cfg = new Config($this->getDataFolder() . $event->getPlayer()->getName() . ".yml", Config::YAML);
            $player = $event->getPlayer();
            $today = new \DateTime("now");
            $now = $today->format("d.m.Y H:i");
            $cfg->set("until", $now);
            $cfg->save();
        }
    }


    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "reward":

                $name = $sender->getName();
                $cfg = new Config($this->getDataFolder() . $name . ".yml", Config::YAML);
                $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $until = $cfg->get("until");
                $today = new \DateTime("now");
                $nopermission = $config->get("no_permission");
                $usage = $config->get("usage");
                $runingame = $config->get("run_ingame");
                $alreadygotreward = $config->get("already_got_reward");
                $alreadygotreward = str_replace("{until}", $until, $alreadygotreward);
                $gotrewardsucces = $config->get("got_reward_succes");
                $rewardcommand = $config->get("command_wich_will_be_executed");
                $timeformat = $config->get("time_format");
                $waittime = $config->get("wait_time");
                $now = $today->format($timeformat);
                $until2 = date($timeformat, strtotime($now . "+" . $waittime));

                if (!$sender instanceof Player) {
                    $sender->sendMessage($runingame);
                    return true;
                }

                if ($sender->hasPermission("rewards.claim")) {
                    if (!empty($args[0])) {
                        $sender->sendMessage("$usage");
                    } else {
                        if ($sender === $now) {
                        } else {
                            if ($now >= $until) {
                                $sender->sendMessage($gotrewardsucces);
                                $rewardcommand = str_replace("{player}", $name, $rewardcommand);
                                $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $rewardcommand);
                                $cfg->set("until", $until2);
                                $cfg->save();
                            } else {
                                $sender->sendMessage("$alreadygotreward");
                            }
                        }
                    }
                } else {
                    $sender->sendMessage($nopermission);
                }
                return true;
        }
    }

    public function onDisable(): void
    {
    }
}
// This Plugin was written by Wertzui123 and you're not allowed to copy or clone it into you're plugin!
// You also musn't change the author or the license.
// © 2019 Wertzui123

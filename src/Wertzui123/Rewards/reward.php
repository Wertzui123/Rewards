<?php

declare(strict_types=1);

namespace Wertzui123\Rewards;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;

class reward extends Command
{

    public $plugin;

public function __construct(Main $plugin)
{
    $c = $plugin->ConfigArray();
parent::__construct($c["command"] ?? "reward", $c["description"] ?? "Claim your reward", $c["usage"] ?? "reward", $c["aliases"] ?? ["claimreward"]);
    $this->setPermission("rewards.claim");
    $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $name = $sender->getName();
        $cfg = new Config($this->plugin->getDataFolder()."players.yml", Config::YAML);
        $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        $until = $cfg->get($name);
        $today = new \DateTime("now");
        $nopermission = $config->get("no_permission");
        $usage = $config->get("usage");
        $runingame = $config->get("run_ingame");
        $alreadygotreward = $config->get("already_got_reward");
        $alreadygotreward = str_replace("{until}", $until, $alreadygotreward);
        $gotrewardsucces = $config->get("got_reward_succes");
        $timeformat = $config->get("time_format");
        $waittime = $config->get("wait_time");
        $now = $today->format($timeformat);
        $until2 = date ($timeformat, strtotime ($now . "+" . $waittime));

        if(!$sender instanceof Player) {
            $sender->sendMessage($runingame);
            return true;
        }
        if($sender->hasPermission("rewards.claim")) {
                    if($now >= $until or $sender->hasPermission("rewards.waiting.bypass")){
                        $sender->sendMessage($gotrewardsucces);
                        foreach($config->get("commands") as $command) {
                            $rewardcommand = str_replace("{player}", $name, $command);
                            $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $rewardcommand);
                        }
                        $cfg->set($name, $until2);
                        $cfg->save();
                    }else {
                        $sender->sendMessage("$alreadygotreward");
                    }
        } else{
            $sender->sendMessage($nopermission);
        }
    }
    }

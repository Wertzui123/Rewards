<?php

declare(strict_types=1);

namespace Wertzui123\Rewards;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\Player;

class reward extends Command
{

    public $plugin;

public function __construct(Main $plugin)
{
    $c = $plugin->ConfigArray();
parent::__construct($c["command"] ?? "reward", $c["description"] ?? "Claim your reward", $c["usage"] ?? "reward", $c["aliases"] ?? ["claimreward"]);
    $this->setPermission("viprewards.claim");
    $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $name = $sender->getName();
        $cfg = new Config($this->plugin->getDataFolder()."players.yml", Config::YAML);
        $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        $until = $cfg->get($name);
        $today = time();
        $nopermission = $config->get("no_permission");
        $runingame = $config->get("run_ingame");
        $gotrewardsucces = $config->get("got_reward_succes");
        $minutes = $config->get("wait_time");
        $waituntil = $today + $minutes * 60;

        if(!$sender instanceof Player) {
            $sender->sendMessage($runingame);
            return true;
        }
        if($sender->hasPermission($this->getPermission())) {
                    if(($today >= $until) || $sender->hasPermission("viprewards.waiting.bypass")){
                        $sender->sendMessage($gotrewardsucces);
                        foreach($config->get("commands") as $command) {
                            $rewardcommand = str_replace(["{player}", "{asplayer}"], [$name, ''], $command);
                            $asplayer = in_array("{asplayer}", explode(" ", $command));
                            if($asplayer == true){
                                $this->plugin->getServer()->dispatchCommand($sender, $rewardcommand);
                            }else{
                                $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $rewardcommand);
                            }
                        }
                        if(!$sender->hasPermission("viprewards.waiting.bypass")){
                            $cfg->set($name, $waituntil);
                            $cfg->save();
                        }
                    }else {
                        $sender->sendMessage($this->plugin->ConvertSeconds($until - $today));
                    }
        } else{
            $sender->sendMessage($nopermission);
        }
        return false;
    }
    }

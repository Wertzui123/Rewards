<?php

declare(strict_types=1);

namespace Wertzui123\Rewards\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use Wertzui123\Rewards\events\RewardClaimEvent;
use Wertzui123\Rewards\Main;

class reward extends Command implements PluginIdentifiableCommand
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin->getConfig()->getNested("command.command"), $plugin->getConfig()->getNested("command.description"), $plugin->getConfig()->getNested("command.usage"), $plugin->getConfig()->getNested("command.aliases"));
        $this->setPermission("rewards.cmd.reward");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("cmd.reward.runIngame"));
            return;
        }
        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->plugin->getMessage("cmd.reward.noPermission"));
            return;
        }
        if (time() < $this->plugin->getWaitTime($sender) && !$sender->hasPermission("rewards.waiting.bypass")) {
            $sender->sendMessage($this->plugin->ConvertSeconds(($this->plugin->playerDataFile->get(strtolower($sender->getName()))["last"] + $this->plugin->getConfig()->getNested('wait_time.' . $this->plugin->getPermissionGroup($sender))) - time(), $this->plugin->getMessage('cmd.reward.wait')));
            return;
        }
        $streak = $this->plugin->getStreak($sender);
        $streakUp = false;
        if($this->plugin->getConfig()->get('reward_streaks') && time() < ($this->plugin->playerDataFile->get(strtolower($sender->getName()))["last"] + ($this->plugin->getConfig()->getNested('wait_time.' . $this->plugin->getPermissionGroup($sender)) * 2))){
            $streak++;
            if($streak > 1){
                $streakUp = true;
            }
        }else{
            $streak = 0;
        }
        $event = new RewardClaimEvent($sender, $this->plugin->getConfig()->getNested("commands." . $this->plugin->getPermissionGroup($sender) . "." . ($streak + 1), $this->plugin->getConfig()->getNested("commands." . $this->plugin->getPermissionGroup($sender) . '.max')), $streak);
        $event->call();
        if($event->isCancelled()) return;
        $sender->sendMessage($this->plugin->getMessage('cmd.reward.success'));
        if($streakUp) $sender->sendMessage($this->plugin->getMessage('cmd.reward.streak', ["{streak}" => $streak]));
        foreach ($event->getCommands() as $command) {
            if (strpos($command, '{asplayer}')) {
                $this->plugin->getServer()->dispatchCommand($sender, str_replace(["{player}", "{asplayer}"], ['"' . $sender->getName() . '"', ''], $command));
            } else {
                $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", '"' . $sender->getName() . '"', $command));
            }
        }
        if (!$sender->hasPermission("rewards.waiting.bypass")) {
            $this->plugin->playerDataFile->set(strtolower($sender->getName()), ["last" => time(), "streak" => $streak]);
        }
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

}
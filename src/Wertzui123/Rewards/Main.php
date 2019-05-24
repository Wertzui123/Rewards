<?php

declare(strict_types=1);

namespace Wertzui123\Rewards;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener{

    public $configversion;

	public function onEnable() : void{ 
	    $this->saveResource("config.yml");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->configversion = 2.0;
        $this->ConfigUpdater();
        $this->getServer()->getCommandMap()->register("Rewards", new reward($this));
	}
	
	
		public function onJoin(PlayerJoinEvent $event)
        {
            if (!$this->isjoinedbefor($event->getPlayer())) {
                $cfg = new Config($this->getDataFolder() . "players.yml", Config::YAML);
                $player = $event->getPlayer();
                $today = new \DateTime("now");
                $f = $this->ConfigArray()["time_format"];
                $now = $today->format($f);
                $cfg->set($player->getName(), $now);
                $cfg->save();
            }
        }
  
	public function isjoinedbefor(Player $player)
    {
        $jc = $this->getJoins();
        if (!$jc->get($player->getName()) == true) {
            $jc->set($player->getName(), true);
            $jc->save();
            return false;
        }else{
            return true;
        }
    }

    public function getJoins(){
	    $f = new Config($this->getDataFolder()."joins.yml", Config::YAML);
	    return $f;
    }

    public function ConfigUpdater()
    {
        if (file_exists($this->getDataFolder() . "config.yml")) {
		$c = $this->ConfigArray();
        $cv = $c["config_version"] ?? 0;
            if ($cv != $this->configversion) {
                $this->getLogger()->info("§cYour Config isn't the latest. §6We renamed your old config to §bconfig-" . $cv . ".yml §6and created a new config.yml. §aHave fun!");
                rename($this->getDataFolder() . "config.yml", "config-" . $cv . ".yml");
                $this->saveResource("config.yml");
            }
        } else {
            $this->saveResource("config.yml");

        }
    }

    public function ConfigArray()
    {
        $c = new Config($this->getDataFolder() . "config.yml");
        $c = $c->getAll();
        return $c;
    }

}
// This Plugin was written by Wertzui123 and you're not allowed to copy or clone it into you're plugin!
// You also musn't change the author or the license.
// © 2019 Wertzui123

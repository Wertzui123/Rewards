<?php

declare(strict_types=1);

namespace Wertzui123\Rewards;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener{

    public $configversion = 3.0;

	public function onEnable() : void{ 
	    $this->saveResource("config.yml");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->ConfigUpdater();
        $this->getServer()->getCommandMap()->register("Rewards", new reward($this));
	}
	
	
		public function onJoin(PlayerJoinEvent $event)
        {
            if (!$this->isjoinedbefor($event->getPlayer())) {
                $cfg = new Config($this->getDataFolder() . "players.yml", Config::YAML);
                $player = $event->getPlayer();
                $cfg->set($player->getName(), 0);
                $cfg->save();
            }
        }
  
	public function isjoinedbefor(Player $player)
    {
        $jc = $this->getJoins();
        if ($jc->get($player->getName()) !== true) {
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

    public function ConvertSeconds(int $seconds){
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        $config = new Config($this->getDataFolder()."config.yml", 2);
        $agr = $config->get("already_got_reward");
        $agr = str_replace("{hours}", $hours, $agr);
        $agr = str_replace("{minutes}", $minutes, $agr);
        $agr = str_replace("{seconds}", $seconds, $agr);
        return $agr;
    }

}
// This Plugin was written by Wertzui123 and you're not allowed to copy or clone it into you're plugin!
// You also musn't change the author or the license.
// © 2019 Wertzui123

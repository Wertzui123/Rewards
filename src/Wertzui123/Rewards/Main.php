<?php

declare(strict_types=1);

namespace Wertzui123\Rewards;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Wertzui123\Rewards\commands\reward;

class Main extends PluginBase{

    /** @var float */
    const CONFIG_VERSION = 4.0;

    /** @var Config */
    public $messagesFile;
    /** @var Config */
    public $playerDataFile;

	public function onEnable() : void{
        $this->ConfigUpdater();
        $this->messagesFile = new Config($this->getDataFolder() . 'messages.yml', Config::YAML);
        $this->playerDataFile = $this->loadDataFile();
        foreach ($this->getConfig()->get('permission_groups') as $group){
            PermissionManager::getInstance()->addPermission(new Permission("rewards.permissions." . $group, "Rewards permission group", Permission::DEFAULT_FALSE));
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("Rewards", new reward($this));
	}

    /**
     * Loads the user data file
     * @return Config|null
     */
	private function loadDataFile(){
	    switch (strtolower($this->getConfig()->get("data_storage", "JSON"))){
            case "json":
                return new Config($this->getDataFolder() . 'playerData.json', Config::JSON);
            case "yaml":
                return new Config($this->getDataFolder() . 'playerData.yml', Config::YAML);
            default:
                $this->getLogger()->warning("Invalid data_storage value. Using JSON.");
                return new Config($this->getDataFolder() . 'playerData.json', Config::JSON);
        }
    }

    /**
     * Returns a message from the messages file
     * @param string $key
     * @param array $replace [optional]
     * @return string
     */
    public function getMessage($key, $replace = []){
	    return str_replace(array_keys($replace), $replace, $this->messagesFile->getNested($key));
    }

    /**
     * @api
     * Returns how long a player still has to wait until they can claim their rewards again
     * @param Player $player
     * @return int
     */
    public function getWaitTime(Player $player){
        return $this->playerDataFile->get(strtolower($player->getName()), ["last" => time(), "streak" => 0])["last"] + $this->getConfig()->getNested('wait_time.' . $this->getPermissionGroup($player));
    }

    /**
     * @api
     * Returns the given players reward streak
     * @param Player $player
     * @return int
     */
    public function getStreak(Player $player){
        return $this->playerDataFile->get(strtolower($player->getName()), ["last" => time(), "streak" => 0])["streak"];
    }

    /**
     * @api
     * Returns the permission group for a player
     * @param Player $player
     * @return string
     */
    public function getPermissionGroup(Player $player){
        foreach ($this->getConfig()->get('permission_groups') as $group){
            if($player->hasPermission("rewards.permissions." . $group)){
                return $group;
            }
        }
        return "default";
    }

    /**
     * Checks whether the config version is the latest and updates it if it isn't
     */
    private function ConfigUpdater()
    {
        if (!file_exists($this->getDataFolder() . "config.yml")) {
            $this->saveResource('config.yml');
            $this->saveResource('messages.yml');
            return;
        }
        if ($this->getConfig()->get('config-version') !== self::CONFIG_VERSION) {
            $config_version = $this->getConfig()->get('config-version');
            $this->getLogger()->info("Your Config isn't the latest. Rewards renamed your old config to §bconfig-" . $config_version . ".yml §6and created a new config. Have fun!");
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config-" . $config_version . ".yml");
            rename($this->getDataFolder() . "messages.yml", $this->getDataFolder() . "messages-" . $config_version . ".yml");
            $this->saveResource("config.yml");
            $this->saveResource("messages.yml");
        }
    }

    /**
     * Converts seconds to hours, minutes and seconds
     * @param int $seconds
     * @param string $message
     * @return string
     */
    public function ConvertSeconds($seconds, $message){
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        return str_replace(["{hours}", "{minutes}", "{seconds}"], [$hours, $minutes, $seconds], $message);
    }

    public function onDisable()
    {
        $this->playerDataFile->save();
    }

}
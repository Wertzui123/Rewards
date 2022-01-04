<?php

namespace Wertzui123\Rewards;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        if (!$this->plugin->playerDataFile->exists(strtolower($event->getPlayer()->getName()))) {
            $this->plugin->playerDataFile->set(strtolower($event->getPlayer()->getName()), ['last' => 0, 'streak' => 0]);
        }
    }

}
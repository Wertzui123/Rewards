<?php

namespace Wertzui123\Rewards\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class RewardClaimEvent extends PlayerEvent implements Cancellable
{
    use CancellableTrait;

    /** @var string[] */
    private $commands;
    /** @var int */
    private $streak;

    /**
     * RewardClaimEvent constructor.
     * @param Player $player
     * @param string[] $commands
     * @param int $streak
     */
    public function __construct(Player $player, array $commands, $streak)
    {
        $this->player = $player;
        $this->commands = $commands;
        $this->streak = $streak;
    }

    /**
     * Returns the commands to execute
     * @return string[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Updates the commands to execute
     * @param string[] $commands
     */
    public function setCommands(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * Returns the reward streak of the player
     * @return int
     */
    public function getStreak()
    {
        return $this->streak;
    }

}
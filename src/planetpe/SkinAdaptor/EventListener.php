<?php

declare(strict_types=1);

namespace planetpe\SkinAdaptor;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\network\mcpe\convert\TypeConverter;

class EventListener implements Listener {
	
	private FixedSkinAdapter $adapter;
	
	public function __construct(FixedSkinAdapter $adapter) {
		$this->adapter = $adapter;
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
		$this->adapter->clearSkinCache($player->getSkin());
	}
}
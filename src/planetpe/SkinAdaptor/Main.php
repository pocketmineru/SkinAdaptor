<?php

declare(strict_types=1);

namespace planetpe\SkinAdaptor;

use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\event\EventPriority;

class Main extends PluginBase implements Listener {

    private static ?FixedSkinAdapter $adapter = null;
    private static bool $debugMode = false;

    protected function onLoad(): void {
        // Сохраняем конфиг если его нет
        $this->saveDefaultConfig();
        
        // Загружаем настройки
        self::$debugMode = $this->getConfig()->get("debug", false);
        
        $this->getLogger()->info("§e[SkinAdaptor] Preparing FixedSkinAdapter...");
        $this->getLogger()->info("§e[SkinAdaptor] Debug mode: " . (self::$debugMode ? "enabled" : "disabled"));
        
        self::$adapter = new FixedSkinAdapter();
    }
    
    public static function isDebugMode(): bool {
        return self::$debugMode;
    }

    protected function onEnable(): void {
        if (self::$adapter === null) {
            self::$adapter = new FixedSkinAdapter();
        }
        TypeConverter::getInstance()->setSkinAdapter(self::$adapter);

        $this->getServer()->getPluginManager()->registerEvent(
            DataPacketReceiveEvent::class,
            function(DataPacketReceiveEvent $event): void {
                $packet = $event->getPacket();
                if ($packet instanceof LoginPacket) {
                    $session = $event->getOrigin();
                    $converter = $session->getTypeConverter();
                    $converter->setSkinAdapter(self::$adapter);
                    
                    if (self::$debugMode) {
                        error_log("[SkinAdaptor] Adapter set for session: " . $session->getIp());
                    }
                }
            },
            EventPriority::LOWEST,
            $this
        );
        
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(self::$adapter), $this);
        $this->getLogger()->info("§a[SkinAdaptor] SkinAdaptor включен!");
    }
}
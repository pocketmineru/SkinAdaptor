# ⚡ SkinAdaptor

**Skin Adapter для PocketMine-MP 5.0+**

Исправь скины персонажей в оффлайн-режиме без Xbox Live авторизации!


---
## 🛒 Заказать плагины
Нужны кастомные плагины для вашего сервера?
📦 [VK](https://vk.me/pocketmine)
---
**⭐ Не забудь поставить звезду на GitHub!**

---

## ✨ Возможности

- 🔧 **Полная поддержка Persona Skins** — скины персонажей работают в оффлайн-режиме
- 🖼️ **HD Skins** — поддержка скинов 256x256 и 512x512
- 🚫 **Без Xbox Live** — работает без авторизации Microsoft
- 💾 **Автоочистка кэша** — экономия памяти сервера
- ⚙️ **Простая настройка** — удобный конфиг
- 🔄 **Seamless** — игроки не замечают подмены

---

## 📋 Требования

| Компонент | Версия |
|----------|--------|
| PocketMine-MP | 5.0.0+ |
| API | 5.0.0+ |
| PHP | 8.1+ |

---

## 📦 Установка

1. Скачай последнюю версию c нашего tg: https://t.me/planetpe/694
2. Распакуй `.phar` в папку `plugins/`
3. Перезапусти сервер
4. Настрой `config.yml` по желанию

---

## ⚙️ Конфигурация

```yaml
# SkinAdaptor Configuration

# Режим отладки (выводит дополнительную информацию в консоль)
debug: false

# Настройки плагина
settings:
  # Очищать кэш скинов при выходе игрока (экономит память)
  clear-cache-on-quit: true

  # Разрешить HD скины (256x256, 512x512)
  # Примечание: HD скины будут уменьшены до 128x128
  # из-за ограничений ядра PocketMine-MP
  allow-hd-skins: true
```

---

## 🎮 Использование

### Команды

| Команда | Описание | Права |
|---------|----------|--------|
| `/skin <скин>` | Изменить свой скин | `skinadaptor.command.skin` |

### Для разработчиков

```php
// Получить SkinAdapter
$adapter = $this->getServer()->getSkinAdapter();

// Проверить, активен ли фикс
if ($adapter instanceof \planetpe\SkinAdaptor\FixedSkinAdapter) {
    // Ваш код
}
```

---

## 📁 Структура

```
SkinAdaptor/
├── plugin.yml          # Основной файл плагина
├── config.yml          # Конфигурация
├── resources/
│   └── config.yml      # Ресурсный конфиг (копируется при установке)
└── src/
    └── planetpe/
        └── SkinAdaptor/
            ├── Main.php              # Главный класс
            ├── EventListener.php     # Обработчик событий
            └── FixedSkinAdapter.php  # Исправленный SkinAdapter
```

---

## 🐛 Известные проблемы

- HD скины автоматически уменьшаются до 128x128 (ограничение PMMP)
- Некоторые кастомные скины могут отображаться некорректно

---

## 🤝 Совместимость

| Плагин | Статус |
|--------|--------|
| NetherGames | ✅ Работает |

---

## 📝 Логирование

При включённом `debug: true`:

```
[SkinAdaptor] PlayerJoined: Steve (Использует Persona Skin)
[SkinAdaptor] SkinApplied: Steve -> Persona_Skin_A
[SkinAdaptor] CacheCleared: Steve
```


## 📄 Лицензия
[MIT License](LICENSE)
---

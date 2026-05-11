<?php

declare(strict_types=1);

namespace planetpe\SkinAdaptor;

use JsonException;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function strlen;
use function in_array;
use function str_repeat;
use function spl_object_id;
use function error_log;
use const JSON_THROW_ON_ERROR;
use planetpe\SkinAdaptor\Main;

class FixedSkinAdapter implements SkinAdapter {

	public function __construct() { }
	
	// Кеш: skinId (lowercase) => SkinData
	private array $personaSkinCache = [];
	
	/**
	 * 64x32 = 8192, 64x64 = 16384, 128x128 = 65536, 256x256 = 262144, 512x512 = 1048576
	 */
	private const VALID_SKIN_SIZES = [8192, 16384, 65536, 262144, 1048576];
	
	/**
	 * @throws JsonException
	 */
	public function toSkinData(Skin $skin): SkinData {
		$skinId = strtolower($skin->getSkinId());
		
		if (Main::isDebugMode()) {
			error_log("[SkinAdaptor DEBUG] >>> toSkinData CALLED <<<");
			error_log("[SkinAdaptor DEBUG] Skin ID: $skinId");
			error_log("[SkinAdaptor DEBUG] Cache size: " . count($this->personaSkinCache) . " entries");
		}
		
		if (isset($this->personaSkinCache[$skinId])) {
			if (Main::isDebugMode()) {
				error_log("[SkinAdaptor DEBUG] ✓ FOUND IN CACHE! Returning original Persona SkinData");
			}
			return $this->personaSkinCache[$skinId];
		}
		
		if (Main::isDebugMode()) {
			error_log("[SkinAdaptor DEBUG] ✗ NOT in cache, using standard conversion");
		}
		
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		
		$geometryName = $skin->getGeometryName();
		if ($geometryName === "") {
			$geometryName = "geometry.humanoid.custom";
		}
		
		return new SkinData(
			$skin->getSkinId(),
			"", // PlayFab ID
			json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR),
			SkinImage::fromLegacy($skin->getSkinData()),
			[], // animations
			$capeImage,
			$skin->getGeometryData()
		);
	}
	
	/**
	 * @throws InvalidSkinException
	 */
	public function fromSkinData(SkinData $data): Skin {
		$isPersona = method_exists($data, 'isPersona') ? $data->isPersona() : false;
		
		if (Main::isDebugMode()) {
			error_log("[SkinAdaptor DEBUG] === fromSkinData CALLED ===");
			error_log("[SkinAdaptor DEBUG] Processing skin ID: " . $data->getSkinId());
			error_log("[SkinAdaptor DEBUG] Is Persona: " . ($isPersona ? "YES" : "NO"));
		}
		
		if ($isPersona) {
			if (Main::isDebugMode()) {
				error_log("[SkinAdaptor DEBUG] >>> Using Persona skin handler <<<");
			}
			
			$skinImageData = $data->getSkinImage()->getData();
			$skinDataLength = strlen($skinImageData);
			
			if (Main::isDebugMode()) {
				error_log("[SkinAdaptor DEBUG] Skin data length: $skinDataLength bytes");
			}
			
			if ($skinDataLength > 65536) {
				if (Main::isDebugMode()) {
					error_log("[SkinAdaptor DEBUG] Large skin detected, downscaling to 128x128");
				}
				$skinImageData = substr($skinImageData, 0, 65536);
			} elseif ($skinDataLength < 8192) {
				if (Main::isDebugMode()) {
					error_log("[SkinAdaptor WARNING] Skin too small, using fallback");
				}
				$skinImageData = str_repeat("\x00\x00\x00\xff", 2048);
			} elseif (!in_array($skinDataLength, [8192, 16384, 65536], true)) {
				if (Main::isDebugMode()) {
					error_log("[SkinAdaptor DEBUG] Non-standard size, padding to 128x128");
				}
				$skinImageData = str_pad($skinImageData, 65536, "\x00\x00\x00\xff");
			}
			
			$capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();
			
			$resourcePatch = json_decode($data->getResourcePatch(), true);
			if (is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
				$geometryName = $resourcePatch["geometry"]["default"];
			} else {
				$geometryName = "geometry.humanoid.custom";
			}
			
			$skin = new Skin(
				$data->getSkinId(),
				$skinImageData,
				$capeData,
				$geometryName,
				$data->getGeometryData()
			);
			
			// Кешируем по skinId (lowercase)
			$cacheKey = strtolower($data->getSkinId());
			$this->personaSkinCache[$cacheKey] = $data;
			
			if (Main::isDebugMode()) {
				error_log("[SkinAdaptor DEBUG] ✓ Cached Persona skin with key: $cacheKey");
				error_log("[SkinAdaptor DEBUG] Persona skin processed successfully!");
			}
			
			return $skin;
		}
		
		if (Main::isDebugMode()) {
			error_log("[SkinAdaptor DEBUG] >>> Using regular skin handler <<<");
		}
		
		
		$capeData = method_exists($data, 'isPersonaCapeOnClassic') && $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();
		
		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if (is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
			$geometryName = $resourcePatch["geometry"]["default"];
		} else {
			throw new InvalidSkinException("Missing geometry name field");
		}
		
		return new Skin(
			$data->getSkinId(),
			$data->getSkinImage()->getData(),
			$capeData,
			$geometryName,
			$data->getGeometryData()
		);
	}
	
	public function clearSkinCache(Skin $skin): void {
		$skinId = strtolower($skin->getSkinId());
		if (isset($this->personaSkinCache[$skinId])) {
			if (Main::isDebugMode()) {
				error_log("[SkinAdaptor DEBUG] Clearing cache for: $skinId");
			}
			unset($this->personaSkinCache[$skinId]);
		}
	}
	
	public function getCacheSize(): int {
		return count($this->personaSkinCache);
	}
}
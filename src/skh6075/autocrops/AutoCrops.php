<?php

namespace skh6075\autocrops;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use skh6075\autocrops\listener\EventListener;


function posToHash(Position $position): string{
    return implode(":", [$position->x, $position->y, $position->z, $position->getLevelNonNull()->getFolderName()]);
}
function hashToPos(string $hash): ?Position{
    [$x, $y, $z, $level] = explode(":", $hash);
    $position = new Position(intval($x), intval($y), intval($z), Server::getInstance()->getLevelByName($level));
    return $position->level instanceof Level ? $position : null;
}

class AutoCrops extends PluginBase{

    /** @var ?AutoCrops */
    private static $instance = null;
    /** @var array */
    protected $config = [];
    /** @var CropData[] */
    private static $cropData = [];


    public static function getInstance(): ?AutoCrops{
        return self::$instance;
    }

    public function onLoad(): void{
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    public function onEnable(): void{
        $this->saveResource("crops.json");
        $this->saveResource("setting.json");
        $this->config = json_decode(file_get_contents($this->getDataFolder() . "setting.json"), true);
        $json = json_decode(file_get_contents($this->getDataFolder() . "crops.json"), true);

        foreach ($json as $hash => $data) {
            self::$cropData[$hash] = CropData::data($data);
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void{
            foreach (self::$cropData as $hash => $cropData) {
                if (time() >= $cropData->getTiming()) {
                    $cropData->setTiming(time() + $this->getCropTiming());
                    $bool = $cropData->onUpdate();
                    if ($bool)
                        unset(self::$cropData[$hash]);
                }
            }
        }), $this->getUpdateTick());
    }

    public function onDisable(): void{
        $data = [];
        foreach (self::$cropData as $hash => $cropData) {
            $data[$hash] = $cropData->jsonSerialize();
        }
        file_put_contents($this->getDataFolder() . "crops.json", json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE));
    }

    public function getUpdateTick(): int{
        return $this->config["update-tick"];
    }

    public function getCropTiming(): int{
        return $this->config["crop-timing"];
    }

    public function getCropData(string $hash): ?CropData{
        return self::$cropData[$hash] ?? null;
    }

    public function addCropData(string $hash, int $blockId, int $timing): void{
        self::$cropData[$hash] = CropData::data([
            "hash" => $hash,
            "blockId" => $blockId,
            "timing" => $timing
        ]);
    }

    public function deleteCropData(string $hash): void{
        if (isset(self::$cropData[$hash])) {
            unset(self::$cropData[$hash]);
        }
    }
}

<?php

namespace skh6075\autocrops\listener;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use skh6075\autocrops\AutoCrops;
use skh6075\autocrops\CropData;
use skh6075\autocrops\CropTypes;
use function skh6075\autocrops\posToHash;

class EventListener implements Listener{

    protected $plugin;


    public function __construct(AutoCrops $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority HIGHEST
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void{
        $item = $event->getItem();
        $block = $event->getBlock();

        if (!$event->isCancelled()) {
            if (in_array($block->getId(), [BlockIds::FARMLAND, BlockIds::SAND])) {
                $block = $block->getSide(Vector3::SIDE_UP);
                if (!isset(CropTypes::CROPS[$item->getId()])) {
                    return;
                }
                if ($this->plugin->getCropData(posToHash($block)) instanceof CropData) {
                    return;
                }
                $timing = time() + $this->plugin->getCropTiming();
                $this->plugin->addCropData(posToHash($block), CropTypes::CROPS[$item->getId()], $timing);
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @priority HIGHEST
     */
    public function onBlockBreak(BlockBreakEvent $event): void{
        $block = $event->getBlock();

        if (!$event->isCancelled()) {
            if ($this->plugin->getCropData(posToHash($block)) instanceof CropData) {
                $this->plugin->deleteCropData(posToHash($block));
            }
        }
    }
}

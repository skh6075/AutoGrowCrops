<?php

namespace skh6075\autocrops;

use pocketmine\block\BlockIds;
use pocketmine\item\ItemIds;

final class CropTypes{

    /** @var int[] */
    public const CROPS = [
        ItemIds::SEEDS => BlockIds::WHEAT_BLOCK,
        ItemIds::CARROT => BlockIds::CARROT_BLOCK,
        ItemIds::POTATO => BlockIds::POTATO_BLOCK,
        ItemIds::BEETROOT => BlockIds::BEETROOT_BLOCK,
        ItemIds::SUGARCANE_BLOCK => BlockIds::SUGARCANE_BLOCK,
        ItemIds::PUMPKIN_SEEDS => BlockIds::PUMPKIN_STEM,
        ItemIds::MELON_SEEDS => BlockIds::MELON_STEM,
        ItemIds::CACTUS => BlockIds::CACTUS
    ];
    /** @var int[] */
    public const TYPE_NORMAL = [
        BlockIds::WHEAT_BLOCK    => 7,
        BlockIds::CARROT_BLOCK   => 7,
        BlockIds::POTATO_BLOCK   => 7,
        BlockIds::BEETROOT_BLOCK => 7
    ];
    /** @var int[] */
    public const TYPE_VERTICAL = [
        BlockIds::SUGARCANE_BLOCK => 3,
        BlockIds::CACTUS          => 3
    ];
    /** @var int[] */
    public const TYPE_HORIZONTAL = [
        BlockIds::PUMPKIN_STEM => 7,
        BlockIds::MELON_STEM   => 7
    ];
    /** @var int[] */
    public const RESULT_HORIZONTAL = [
        BlockIds::PUMPKIN_STEM => BlockIds::PUMPKIN,
        BlockIds::MELON_STEM   => BlockIds::MELON_BLOCK
    ];
}
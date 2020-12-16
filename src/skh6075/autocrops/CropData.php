<?php

namespace skh6075\autocrops;

use pocketmine\block\BlockFactory;
use pocketmine\level\Position;

class CropData implements \JsonSerializable{

    /** @var string */
    protected $hash;
    /** @var int */
    protected $blockId;
    /** @var int */
    protected $timing;


    public function __construct(string $hash, int $blockId, int $timing) {
        $this->hash = $hash;
        $this->blockId = $blockId;
        $this->timing = $timing;
    }

    public static function data(array $data): self{
        return new CropData(
            (string) $data["hash"],
            (int) $data["blockId"],
            (int) $data["timing"]
        );
    }

    public function jsonSerialize(): array{
        return [
            "hash" => $this->hash,
            "blockId" => $this->blockId,
            "timing" => $this->timing
        ];
    }

    public function getHash(): string{
        return $this->hash;
    }

    public function getBlockId(): int{
        return $this->blockId;
    }

    public function getTiming(): int{
        return $this->timing;
    }

    public function setTiming(int $timing): void{
        $this->timing = $timing;
    }

    public function onUpdate(): bool{
        if (($position = hashToPos($this->hash)) instanceof Position) {
            /** @var Position $position */
            $this->loadChunk($position);
            if (in_array($this->blockId, CropTypes::TYPE_NORMAL)) {
                $maxMeta = CropTypes::TYPE_NORMAL[$this->blockId];
                $nowMeta = $position->getLevel()->getBlockDataAt($position->x, $position->y, $position->z);
                if ($nowMeta >= $maxMeta) {
                    $position->getLevel()->setBlock($position->asVector3(), BlockFactory::get($this->blockId, $maxMeta), true, true);
                    return true;
                } else {
                    $position->getLevel()->setBlock($position->asVector3(), BlockFactory::get($this->blockId, ($nowMeta + 1)), true, true);
                }
            } else if (in_array($this->blockId, CropTypes::TYPE_VERTICAL)) {
                $maxMeta = CropTypes::TYPE_VERTICAL[$this->blockId];
                for ($i = 0; $i < $maxMeta; $i ++) {
                    $position->setComponents($position->x, $position->y + $i, $position->z);
                    if ($position->y >= 256) {
                        break;
                    }
                    $position->getLevel()->setBlock($position->asVector3(), BlockFactory::get($this->blockId), true, true);
                }
                return true;
            } else if (in_array($this->blockId, CropTypes::TYPE_HORIZONTAL)) {
                $maxMeta = CropTypes::TYPE_HORIZONTAL[$this->blockId];
                $nowMeta = $position->getLevel()->getBlockDataAt($position->x, $position->y, $position->z);
                if ($nowMeta >= 7) {
                    for ($i = 2; $i <= 5; $i ++) {
                        $side = $position->getSide($i);
                        if ($side->getLevel()->getBlockIdAt($side->x, $side->y, $side->z) === CropTypes::RESULT_HORIZONTAL[$this->blockId]) {
                            break;
                        }
                    }
                    $canReplacedSide = -1;
                    for ($i = 2; $i <= 5; $i ++) {
                        $side = $position->getSide($i);
                        if ($side->getLevel()->getBlockIdAt($side->x, $side->y, $side->z) !== CropTypes::RESULT_HORIZONTAL[$this->blockId]) {
                            $canReplacedSide = $side;
                            break;
                        }
                    }
                    if ($canReplacedSide !== -1) {
                        $side = $position->getSide($canReplacedSide);
                        $position->getLevel()->setBlock($side->asVector3(), BlockFactory::get(CropTypes::RESULT_HORIZONTAL[$this->blockId]), true, true);
                    }
                    return true;
                } else {
                    $position->getLevel()->setBlock($position->asVector3(), BlockFactory::get($this->blockId, ($nowMeta + 1)), true, true);
                }
            }
        }
        return false;
    }

    private function loadChunk(Position &$position): void{
        if (!$position->getLevel()->isChunkLoaded($position->x, $position->z)) {
            $position->getLevel()->loadChunk($position->x, $position->z, true);
        }
        $chunk = $position->getLevel()->getChunk($position->x >> 4, $position->z >> 4, true);
        if (!$chunk->isGenerated()) $chunk->setGenerated(true);
        if (!$chunk->isPopulated()) $chunk->setPopulated(true);
    }
}
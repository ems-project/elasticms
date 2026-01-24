<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Xliff\Id\IdGeneratorInterface;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Xliff;

class Document
{
    /** @var Unit[] */
    private array $units = [];

    public function __construct(private readonly IdGeneratorInterface $idGenerator, public readonly string $id)
    {
    }

    public function createText(string $resourceName, string $source, ?string $target = null, ?string $baseline = null): Unit
    {
        if (null === $target) {
            $state = Xliff::STATE_NEW;
        } elseif ($baseline === $source) {
            $state = Xliff::STATE_FINAL;
        } else {
            $state = Xliff::STATE_NEEDS_TRANSLATION;
        }

        $unit = new Unit(
            id: $this->idGenerator->nextUnitId(),
            resourceName: $resourceName,
            type: 'text'
        );

        $sourceNodes = [
            new Text($source),
        ];

        $targetNodes = [];
        if (null !== $target) {
            $targetNodes[] = new Text($target);
        }

        $segment = new Segment(
            sourceNodes: $sourceNodes,
            targetNodes: $targetNodes,
            state: $state
        );

        $unit->addSegment($segment);
        $this->addUnit($unit);

        return $unit;
    }

    /**
     * @return Unit[]
     */
    public function getUnits(): array
    {
        return $this->units;
    }

    public function addUnit(Unit $unit): void
    {
        $this->units[] = $unit;
    }
}

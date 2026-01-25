<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Xliff\Html\HtmlExtractor;
use EMS\Xliff\Id\IdGeneratorInterface;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Xliff;

class Document
{
    /** @var DocumentNodeInterface[] */
    private array $nodes = [];
    private HtmlExtractor $htmlExtractor;

    public function __construct(private readonly IdGeneratorInterface $idGenerator, public readonly string $id)
    {
        $this->htmlExtractor = new HtmlExtractor($idGenerator);
    }

    public function createText(string $resourceName, string $source, ?string $target = null, ?string $baseline = null, bool $isFinal = false): Unit
    {
        if (null === $target) {
            $state = Xliff::STATE_NEW;
        } elseif ($baseline === $source) {
            $state = Xliff::STATE_FINAL;
        } else {
            $state = Xliff::STATE_NEEDS_TRANSLATION;
        }
        if ($isFinal) {
            $state = Xliff::STATE_FINAL;
        }

        $unit = new Unit(
            id: $this->idGenerator->nextUnitId(),
            resourceName: $resourceName,
            type: 'text',
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
        $this->addNode($unit);

        return $unit;
    }

    public function createHtml(string $resourceName, string $sourceHtml, ?string $targetHtml = null, ?string $baselineHtml = null, bool $isFinal = false): void
    {
        $unitGroup = $this->htmlExtractor->extract($resourceName, $sourceHtml, $targetHtml, $baselineHtml);

        $this->addNode($unitGroup);
    }

    /**
     * @return DocumentNodeInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function addNode(DocumentNodeInterface $node): void
    {
        $this->nodes[] = $node;
    }
}

<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Xliff;

class Segment
{
    /** @var string[] */
    private array $sources = [];
    /** @var string[] */
    private array $baselines = [];

    /**
     * @param Node[] $sourceNodes
     * @param Node[] $targetNodes
     */
    private function __construct(private array $sourceNodes, private array $targetNodes, private readonly ?string $state = null, ?string $source = null, ?string $baselines = null, private readonly bool $isFinal = false)
    {
        if (null !== $source) {
            $this->sources[] = $source;
        }
        if (null !== $baselines) {
            $this->baselines[] = $baselines;
        }
    }

    /**
     * @param Node[] $sourceNodes
     * @param Node[] $targetNodes
     */
    public static function load(array $sourceNodes, array $targetNodes, string $state): self
    {
        return new self($sourceNodes, $targetNodes, $state);
    }

    /**
     * @param Node[] $sourceNodes
     * @param Node[] $targetNodes
     */
    public static function init(array $sourceNodes = [], array $targetNodes = [], ?string $source = null, ?string $baselines = null, bool $isFinal = false): self
    {
        return new self($sourceNodes, $targetNodes, null, $source, $baselines, $isFinal);
    }

    /**
     * @return Node[]
     */
    public function getSourceNodes(): array
    {
        return $this->sourceNodes;
    }

    /**
     * @return Node[]
     */
    public function getTargetNodes(): array
    {
        return $this->targetNodes;
    }

    /**
     * @param Node[] $nodes
     */
    public function addSourceNodes(array $nodes): void
    {
        $this->sourceNodes = \array_merge($this->sourceNodes, $nodes);
    }

    /**
     * @param Node[] $nodes
     */
    public function addTargetNodes(array $nodes): void
    {
        $this->targetNodes = \array_merge($this->targetNodes, $nodes);
    }

    public function getState(): string
    {
        if (null !== $this->state) {
            return $this->state;
        }

        if ([] === $this->targetNodes && 1 === \count($this->sourceNodes) && $this->sourceNodes[0] instanceof Text && '' === $this->sourceNodes[0]->text) {
            return Xliff::STATE_FINAL;
        }
        if ($this->isFinal) {
            return [] === $this->targetNodes ? Xliff::STATE_NEW : Xliff::STATE_FINAL;
        }
        if ([] === $this->targetNodes) {
            return Xliff::STATE_NEW;
        }
        if ([] === $this->baselines) {
            return Xliff::STATE_NEEDS_TRANSLATION;
        }
        if (\count($this->sources) !== \count($this->baselines)) {
            return Xliff::STATE_NEEDS_TRANSLATION;
        }
        $i = 0;
        foreach ($this->sources as $source) {
            if ($this->baselines[$i++] === $source) {
                continue;
            }

            return Xliff::STATE_NEEDS_TRANSLATION;
        }

        return Xliff::STATE_FINAL;
    }

    public function addBaseline(string $source, string $baseline): void
    {
        $this->sources[] = $source;
        $this->baselines[] = $baseline;
    }
}

<?php

declare(strict_types=1);

namespace EMS\FormBundle\FormConfig;

class FieldChoicesConfig
{
    /** @var mixed[] */
    private readonly array $values;
    /** @var mixed[] */
    private array $labels;
    /** @var mixed[] */
    private array $choices = [];
    private ?string $placeholder = null;
    private ?string $sort = null;

    /**
     * @param mixed[] $values
     * @param mixed[] $labels
     */
    public function __construct(private readonly string $id, array $values, array $labels)
    {
        if (\count($labels) > \count($values)) {
            $this->placeholder = \array_shift($labels);
        }

        if (\count($values) !== \count($labels)) {
            throw new \Exception(\sprintf('Invalid choice list: %d values != %d labels!', \count($values), \count($labels)));
        }
        $this->values = $values;
        $this->labels = $labels;
        $this->sort = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getLabel(string $value): string
    {
        $index = \array_search($value, $this->values);
        if (!\is_string($this->labels[$index] ?? null)) {
            return $value;
        }

        return $this->labels[$index];
    }

    public function listLabel(): string
    {
        $choices = $this->choices;
        $choice = \array_pop($choices);

        $list = $this->combineValuesAndLabels($this->values, $this->labels, $choices);

        return \array_flip($list)[$choice] ?? '';
    }

    /** @return array<string, string> */
    public function list(): array
    {
        return $this->combineValuesAndLabels($this->values, $this->labels, $this->choices);
    }

    public function addChoice(string $choice): void
    {
        if (!isset(\array_flip($this->list())[$choice])) {
            throw new \Exception('invalid choice: happens when previous level choices are changed without ajax calls');
        }

        $this->choices[] = $choice;
    }

    public function isMultiLevel(): bool
    {
        return $this->calculateMaxLevel($this->values) > 0;
    }

    public function getMaxLevel(): int
    {
        return $this->calculateMaxLevel($this->values);
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    /** @param mixed[] $choices */
    private function calculateMaxLevel(array $choices): int
    {
        $level = 0;
        foreach ($choices as $choice) {
            if (\is_array($choice)) {
                $level = \max(
                    $level,
                    1 + $this->calculateMaxLevel($choice[\array_key_first($choice)])
                );
            }
        }

        return $level;
    }

    /**
     * @param mixed[] $elements
     *
     * @return mixed[]
     */
    private function getTopLevel(array $elements): array
    {
        return \array_map(
            function ($element) {
                if (\is_array($element)) {
                    return \array_key_first($element);
                }

                return $element;
            },
            $elements
        );
    }

    /**
     * @param mixed[] $values
     * @param mixed[] $labels
     * @param mixed[] $choices
     *
     * @return mixed[]
     */
    private function combineValuesAndLabels(array $values, array $labels, array $choices): array
    {
        foreach ($choices as $choice) {
            $idx = \array_search($choice, $this->getTopLevel($values));
            if (false === $idx) {
                continue;
            }
            $values = $values[$idx];
            $labels = $labels[$idx];

            if (!\is_array($values) || !\is_array($labels)) {
                return [];
            }

            $values = \reset($values);
            $labels = \reset($labels);

            if (false === $values || false === $labels) {
                return [];
            }
        }

        $list = \array_combine($this->getTopLevel($labels), $this->getTopLevel($values));

        return $this->sort($list);
    }

    /**
     * @param array<string, string> $list
     *
     * @return array<string, ?string>
     */
    private function sort(array $list): array
    {
        $firstKey = \array_key_first($list);
        /** @var string|null $firstValue */
        $firstValue = $list[$firstKey] ?? null;

        if (null === $firstValue || '' === $firstValue) {
            \array_shift($list); // do not sort placeholder
        }

        if ('label_alpha' === $this->sort) {
            $collator = new \Collator('en');
            \uksort($list, fn ($a, $b) => (int) $collator->compare($a, $b));
        }
        if ('value_alpha' === $this->sort) {
            $collator = new \Collator('en');
            \uasort($list, fn ($a, $b) => (int) $collator->compare($a, $b));
        }

        if (null === $firstValue || '' === $firstValue) {
            $list = \array_merge([$firstKey => $firstValue], $list); // merge placeholder back
        }

        return $list;
    }
}

<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Helpers\PropertyAccess\PropertyAccessor;
use EMS\Helpers\Standard\Accessor;
use EMS\Helpers\Standard\Type;
use EMS\Xliff\Formater\FormaterInterface;
use EMS\Xliff\Formater\HtmlFormater;
use EMS\Xliff\Formater\TextFormater;
use EMS\Xliff\Html\HtmlExtractor;
use EMS\Xliff\Html\HtmlInjector;
use EMS\Xliff\Id\IdGeneratorInterface;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Report\InsertReport;

class Document
{
    final public const string LOCALE_PLACE_HOLDER = '%locale%';
    /** @var DocumentNodeInterface[] */
    private array $nodes = [];
    private readonly HtmlExtractor $htmlExtractor;
    private readonly HtmlFormater $htmlFormater;
    private readonly TextFormater $textFormater;
    private readonly PropertyAccessor $propertyAccessor;
    private readonly HtmlInjector $htmlInjector;

    public function __construct(private readonly IdGeneratorInterface $idGenerator, public readonly string $id, private readonly ?InsertReport $insertReport = null)
    {
        $this->htmlExtractor = new HtmlExtractor($idGenerator);
        $this->htmlFormater = new HtmlFormater();
        $this->textFormater = new TextFormater();
        $this->propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $this->htmlInjector = new HtmlInjector();
    }

    public function createText(string $resourceName, string $source, ?string $target = null, ?string $baseline = null, bool $isFinal = false): Unit
    {
        $unit = new Unit(
            id: $this->idGenerator->nextUnitId(),
            resourceName: $resourceName,
        );

        $sourceNodes = [
            new Text($source),
        ];

        $targetNodes = [];
        if (null !== $target) {
            $targetNodes[] = new Text($target);
        }

        $segment = Segment::init(
            sourceNodes: $sourceNodes,
            targetNodes: $targetNodes,
            source: $source,
            baselines: $baseline,
            isFinal : $isFinal,
        );

        $unit->addSegment($segment);
        $this->addNode($unit);

        return $unit;
    }

    public function createHtml(string $resourceName, string $sourceHtml, ?string $targetHtml = null, ?string $baselineHtml = null, bool $isFinal = false): void
    {
        $unitGroup = $this->htmlExtractor->extract($resourceName, $sourceHtml, $targetHtml, $baselineHtml, $isFinal);

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

    /**
     * @return array<string, string>
     */
    public function extract(): array
    {
        $associativeArray = [];
        foreach ($this->getNodes() as $node) {
            $associativeArray[Type::string($node->getResourceName())] = 'extract';
        }

        return $associativeArray;
    }

    /**
     * @param mixed[] $extractData
     * @param mixed[] $insertData
     */
    public function unitToAssociativeArray(Package $package, array &$extractData, array &$insertData): void
    {
        foreach ($this->getNodes() as $node) {
            match ($node::class) {
                Unit::class => $this->insertSimpleField($package, $node, $extractData, $insertData),
                UnitGroup::class => $this->insertHtmlField($package, $node, $extractData, $insertData),
                default => throw new \RuntimeException(\sprintf('Unexpected %s unit type', $node::class)),
            };
        }
    }

    /**
     * @param mixed[] $extractData
     * @param mixed[] $insertData
     */
    private function insertSimpleField(Package $package, Unit $unit, array &$extractData, array &$insertData): void
    {
        $sourceNodes = [];
        $targetNodes = [];
        foreach ($unit->getSegments() as $segment) {
            foreach ($segment->getSourceNodes() as $sourceNode) {
                if (!$sourceNode instanceof Text) {
                    throw new \RuntimeException(\sprintf('Unexpected non Text node: %s', $sourceNode::class));
                }
                $sourceNodes[] = $sourceNode->text;
            }
            foreach ($segment->getTargetNodes() as $targetNode) {
                if (!$targetNode instanceof Text) {
                    throw new \RuntimeException(\sprintf('Unexpected non Text node: %s', $targetNode::class));
                }
                $targetNodes[] = $targetNode->text;
            }
        }
        $this->insertField($package, $unit, $extractData, \implode('', $sourceNodes), $insertData, \implode('', $targetNodes), $this->textFormater);
    }

    /**
     * @param mixed[] $extractData
     * @param mixed[] $insertData
     */
    private function insertHtmlField(Package $package, UnitGroup $unitGroup, array &$extractData, array &$insertData): void
    {
        $source = $this->htmlInjector->inject($unitGroup, 'source');
        $target = $this->htmlInjector->inject($unitGroup, 'target');
        $this->insertField($package, $unitGroup, $extractData, $source, $insertData, $target, $this->htmlFormater);
    }

    /**
     * @param mixed[] $extractData
     * @param mixed[] $insertData
     */
    private function insertField(Package $package, DocumentNodeInterface $documentNode, array &$extractData, string $source, array &$insertData, string $target, FormaterInterface $formater): void
    {
        $propertyPath = Accessor::fieldPathToPropertyPath(Type::string($documentNode->getResourceName()));
        $sourcePropertyPath = \str_replace(self::LOCALE_PLACE_HOLDER, $package->getSourceLocale(), $propertyPath);
        $targetPropertyPath = \str_replace(self::LOCALE_PLACE_HOLDER, $package->getTargetLocale(), $propertyPath);

        $expectedSource = $this->propertyAccessor->getValue($extractData, $sourcePropertyPath);
        $expectedSource = $formater->format($expectedSource ?? '');

        $source = $formater->format($source);

        if (\trim($expectedSource) !== \trim($source)) {
            if (null === $this->insertReport) {
                throw new \RuntimeException('Unexpected null insert report');
            }
            $this->insertReport->addError($expectedSource, $source, $sourcePropertyPath, $this->id);
        }
        $this->propertyAccessor->setValue($insertData, $targetPropertyPath, $target);
    }

    /**
     * @return Segment[]
     */
    public function getSegments(): array
    {
        $segments = [];
        foreach ($this->getNodes() as $node) {
            $segments = [...$segments, ...$node->getSegments()];
        }

        return $segments;
    }
}

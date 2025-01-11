<?php

declare(strict_types=1);

namespace App\CLI\Client\Data\Column;

use App\CLI\Client\Data\Data;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use EMS\CommonBundle\Common\CoreApi\Search\Scroll;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Search\Search;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DataColumnBusinessId extends DataColumn
{
    public string $field;
    public string $contentType;
    public int $scrollSize;
    /** @var ?array<mixed> */
    private readonly ?array $scrollMust;
    private readonly bool $removeNotFound;

    private int $notFound = 0;
    /** @var array<mixed> */
    private array $notFoundValues = [];

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        /** @var array{index: int, field: string, contentType: string, scrollSize: int, scrollMust: ?array<mixed>, removeNotFound: bool} $options */
        $options = $this->getOptionsResolver()->resolve($config);

        parent::__construct($options['index']);
        $this->field = $options['field'];
        $this->contentType = $options['contentType'];
        $this->scrollSize = $options['scrollSize'];
        $this->removeNotFound = $options['removeNotFound'];
        $this->scrollMust = $options['scrollMust'];
    }

    #[\Override]
    protected function getOptionsResolver(): OptionsResolver
    {
        $optionsResolver = parent::getOptionsResolver();
        $optionsResolver
            ->setRequired(['field', 'contentType'])
            ->setDefaults([
                'scrollSize' => 1000,
                'scrollMust' => null,
                'removeNotFound' => false,
            ])
            ->setAllowedTypes('removeNotFound', ['bool'])
        ;

        return $optionsResolver;
    }

    #[\Override]
    public function transform(Data $data, TransformContext $transformContext): void
    {
        parent::transform($data, $transformContext);

        $io = $transformContext->io;
        $io->writeln(\vsprintf('Transforming businessId “%s” for column index %d', [
            $this->contentType,
            $this->columnIndex,
        ]));

        $progressScroll = $io->createProgressBar();
        $scroll = $this->createScroll($transformContext->coreApi);

        foreach ($scroll as $result) {
            if ($businessId = $result->getEMSSource()->get($this->field)) {
                $data->searchAndReplace($this->columnIndex, $businessId, $result->getEmsId());
            }
            $progressScroll->advance();
        }

        $progressScroll->finish();

        if ($this->removeNotFound) {
            $this->removeNotFound($data, $io);
        }

        $io->newLine(2);
    }

    private function createScroll(CoreApiInterface $coreApi): Scroll
    {
        $environmentAlias = $coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new Exists($this->field));
        if (null !== $this->scrollMust) {
            $boolQuery->addMust($this->scrollMust);
        }

        $search = new Search([$environmentAlias], $boolQuery);
        $search->setContentTypes([$this->contentType]);
        $search->setSources([$this->field]);

        return $coreApi->search()->scroll($search, $this->scrollSize);
    }

    private function removeNotFound(Data $data, SymfonyStyle $io): void
    {
        $data->filter(function (array $row) {
            if (EMSLink::fromText((string) $row[$this->columnIndex])->isValid()) {
                return true;
            }

            ++$this->notFound;
            if (!\in_array($row[$this->columnIndex], $this->notFoundValues)) {
                $this->notFoundValues[] = $row[$this->columnIndex];
            }

            return false;
        });

        $io->note(\sprintf('Removed %d rows with %s invalid values', $this->notFound, \count($this->notFoundValues)));
    }
}

<?php

declare(strict_types=1);

namespace App\CLI\Client\Document\Update;

use App\CLI\Client\Data\Column\TransformContext;
use App\CLI\Client\Data\Data;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class DocumentUpdater
{
    public function __construct(private Data $data, private DocumentUpdateConfig $config, private CoreApiInterface $coreApi, private SymfonyStyle $io, private bool $dryRun)
    {
    }

    public function executeColumnTransformers(): self
    {
        $this->io->section('Executing data column transformers');

        $transformContext = new TransformContext($this->coreApi, $this->io);

        foreach ($this->config->dataColumns as $dataColumn) {
            $dataColumn->transform($this->data, $transformContext);
        }

        return $this;
    }

    public function execute(): self
    {
        $collectionField = $this->config->getCollectionField();
        if (null !== $collectionField) {
            $this->executeGroupedUpdates($collectionField);
        } else {
            $this->executeUpdates();
        }

        return $this;
    }

    /**
     * @param array<mixed> $row
     */
    private function getOuuidFromRow(array $row): string
    {
        $updateIndexEmsId = $this->config->updateIndexEmsId;

        $emsId = $row[$updateIndexEmsId] ?? null;

        if (null === $emsId) {
            throw new \RuntimeException(\sprintf('Row does not contain emsId in column [%d]', $updateIndexEmsId));
        }

        $emsId = Type::string($emsId);

        return EMSLink::fromText($emsId)->getOuuid();
    }

    /**
     * @param array<mixed> $row
     *
     * @return array<mixed>
     */
    private function getRawDataFromRow(array $row): array
    {
        $updateMapping = $this->config->updateMapping;

        $rawData = [];
        foreach ($updateMapping as $updateMap) {
            $updateValue = $row[$updateMap->indexDataColumn] ?? null;

            if (null === $updateValue) {
                throw new \RuntimeException('Row does not contain update value in column [%d]', $updateMap->indexDataColumn);
            }

            $rawData[$updateMap->field] = $updateValue;
        }

        if (0 === \count($rawData)) {
            throw new \RuntimeException('No update found!');
        }

        return $rawData;
    }

    public function executeUpdates(): void
    {
        $this->io->section('Executing update');

        $dataApi = $this->coreApi->data($this->config->updateContentType);
        $dataProgress = $this->io->createProgressBar(\count($this->data));

        foreach ($this->data as $i => $row) {
            try {
                $ouuid = $this->getOuuidFromRow($row);
                $rawData = $this->getRawDataFromRow($row);
                if ($this->io->isVerbose()) {
                    $this->io->note(\sprintf('Update document %s', $ouuid));
                    $this->io->note(Json::encode($rawData, true));
                }
                if (!$this->dryRun) {
                    $dataApi->save($ouuid, $rawData, DataInterface::MODE_UPDATE, false);
                }
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Error in row %d with ouuid %s', $i, $ouuid ?? '??'));
                if ($this->io->isDebug()) {
                    $this->io->error($e->getMessage());
                }
            }

            $dataProgress->advance();
        }
    }

    public function executeGroupedUpdates(string $collectionField): void
    {
        $this->io->section('Executing update');

        $dataApi = $this->coreApi->data($this->config->updateContentType);
        $this->data->groupByColumn($this->config->updateIndexEmsId);
        $dataProgress = $this->io->createProgressBar(\count($this->data));
        foreach ($this->data as $rows) {
            $ouuid = null;
            $rawData = [];
            foreach ($rows as $row) {
                $ouuid = $this->getOuuidFromRow($row);
                $rawData[] = $this->getRawDataFromRow($row);
            }
            if ($this->io->isVerbose()) {
                $this->io->note(\sprintf('Update the collection %s document %s', $collectionField, $ouuid));
                $this->io->note(Json::encode($rawData, true));
            }
            if (null === $ouuid) {
                throw new \RuntimeException('Unexpected null ouuid');
            }
            if (!$this->dryRun) {
                $dataApi->save($ouuid, [
                    $collectionField => $rawData,
                ], DataInterface::MODE_UPDATE, false);
            }
            $dataProgress->advance();
        }
        $dataProgress->finish();
    }
}

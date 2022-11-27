<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Update;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Extract\ExtractedData;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use EMS\CommonBundle\Common\Standard\Json;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Psr\Log\LoggerInterface;

class UpdateManager
{
    private CoreApiInterface $coreApi;
    private ConfigManager $configManager;
    private LoggerInterface $logger;
    private bool $dryRun;

    public function __construct(CoreApiInterface $coreApi, ConfigManager $configManager, LoggerInterface $logger, bool $dryRun)
    {
        $this->coreApi = $coreApi;
        $this->configManager = $configManager;
        $this->logger = $logger;
        $this->dryRun = $dryRun;
    }

    public function update(ExtractedData $extractedData, bool $force, Rapport $rapport): void
    {
        $ouuid = $extractedData->getDocument()->getOuuid();
        $data = $extractedData->getData();
        $type = $this->configManager->getType($extractedData->getDocument()->getType());
        $typeManager = $this->coreApi->data($extractedData->getDocument()->getType());
        if (!$typeManager->head($ouuid)) {
            $data = \array_merge_recursive($type->getDefaultData(), $data);
            $this->logger->debug(Json::encode($data, true));
            $rapport->addNewDocument($extractedData->getDocument());
            if ($this->dryRun) {
                return;
            }
            $draft = $typeManager->create($data, $ouuid);
            try {
                $ouuid = $typeManager->finalize($draft->getRevisionId());
                $this->configManager->setLastUpdated($ouuid);
            } catch (CoreApiExceptionInterface $e) {
                $typeManager->discard($draft->getRevisionId());
            }
            $extractedData->getDocument()->setOuuid($ouuid);
        } else {
            $hash = $data[$this->configManager->getHashResourcesField()] ?? null;
            if (!$force && null !== $hash && $hash === ($typeManager->get($ouuid)->getRawData()[$this->configManager->getHashResourcesField()] ?? null)) {
                return;
            }
            try {
                $this->logger->debug(Json::encode($data, true));
                $rapport->addUpdateDocument($extractedData->getDocument());
                if ($this->dryRun) {
                    return;
                }
                $typeManager->save($ouuid, $data);
                $this->configManager->setLastUpdated($ouuid);
            } catch (\Throwable $e) {
                $this->logger->error(\sprintf('Impossible to finalize the document %s with the error %s', $ouuid, $e->getMessage()));
            }
        }
    }
}

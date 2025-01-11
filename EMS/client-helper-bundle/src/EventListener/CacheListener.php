<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\EventListener;

use EMS\ClientHelperBundle\Controller\CacheController;
use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Cache\CacheResponse;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\CommonBundle\Contracts\Elasticsearch\QueryLoggerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class CacheListener implements EventSubscriberInterface
{
    public function __construct(private CacheHelper $cacheHelper, private CacheController $cacheController, private Kernel $kernel, private LoggerInterface $logger, private QueryLoggerInterface $queryLogger)
    {
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [['cacheRequest', 300]],
            KernelEvents::TERMINATE => [['terminate', 300]],
            KernelEvents::EXCEPTION => [['exception', 300]],
        ];
    }

    public function cacheRequest(ControllerEvent $event): void
    {
        if (EmschRequest::fromRequest($event->getRequest())->hasEmschCache()) {
            $this->logger->debug('Changing controller for checking cache');
            $event->setController($this->cacheController);
        }
    }

    public function terminate(TerminateEvent $event): void
    {
        $response = $event->getResponse();
        $emschRequest = EmschRequest::fromRequest($event->getRequest());

        if ($response->isSuccessful()
            && $emschRequest->hasEmschCache()
            && !$response->headers->has(CacheResponse::HEADER_X_EMSCH_CACHE)) {
            $this->subRequest($emschRequest);
        }
    }

    public function exception(ExceptionEvent $event): void
    {
        $emschRequest = EmschRequest::fromRequest($event->getRequest());

        if ($emschRequest->hasEmschCache()) {
            $response = CacheResponse::fromException($event->getThrowable());
            $this->cacheHelper->saveResponse($response, $emschRequest->getEmschCacheKey());
        }
    }

    private function subRequest(EmschRequest $emschRequest): void
    {
        $emschCacheKey = $emschRequest->getEmschCacheKey();
        $this->logger->info(\sprintf('Starting sub request for %s', $emschCacheKey));

        $emschRequest->closeSession();
        $subRequest = EmschRequest::fromRequest($emschRequest->duplicate());
        $subRequest->makeSubRequest();

        \set_time_limit($emschRequest->getEmschCacheLimit());
        $this->queryLogger->disable();

        $this->cacheHelper->saveResponse(CacheResponse::isRunning(), $emschCacheKey);
        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $this->cacheHelper->saveResponse(CacheResponse::fromSubRequest($response), $emschRequest->getEmschCacheKey());

        $this->logger->info(\sprintf('Finished sub request for %s', $emschCacheKey));
    }
}

<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Messenger\Handler;

use EMS\CommonBundle\Common\Ai\OpenAiRequest;
use EMS\CommonBundle\Common\Ai\OpenAiService;
use EMS\CoreBundle\Core\Action\ActionService;
use EMS\CoreBundle\Core\Messenger\Message\ActionMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ActionHandler
{
    public function __construct(
        private readonly ActionService $actionService,
        private readonly OpenAiService $openAiService
    ) {
    }

    public function __invoke(ActionMessage $message): void
    {
        $action = $this->actionService->getById($message->actionId);

        try {
            $this->actionService->statusInProgress($action);

            $openAiRequest = new OpenAiRequest($action->getRequest());
            $response = $this->openAiService->v1Responses($openAiRequest)->toArray();

            $this->actionService->statusDone($action, $response);
        } catch (\Throwable $e) {
            $this->actionService->statusFailed($action, $e);
        }
    }
}

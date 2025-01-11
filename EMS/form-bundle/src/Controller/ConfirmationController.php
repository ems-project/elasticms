<?php

declare(strict_types=1);

namespace EMS\FormBundle\Controller;

use EMS\FormBundle\Security\Guard;
use EMS\FormBundle\Service\Confirmation\ConfirmationRequest;
use EMS\FormBundle\Service\Confirmation\ConfirmationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class ConfirmationController
{
    public function __construct(private Guard $guard, private ConfirmationService $confirmationService, private LoggerInterface $logger)
    {
    }

    public function postSend(Request $request, string $ouuid): Response
    {
        return $this->send($request, $ouuid);
    }

    public function postDebug(Request $request, string $ouuid): Response
    {
        return $this->send($request, $ouuid, true);
    }

    private function send(Request $request, string $ouuid, bool $debug = false): Response
    {
        $response = [
            'instruction' => 'send-confirmation',
            'response' => false,
            'ouuid' => $ouuid,
            'codeField' => 'unknown',
            'emsStatus' => 200,
            'message' => null,
        ];

        try {
            $confirmationRequest = new ConfirmationRequest($request);

            if (!$debug && !$this->guard->checkToken($request, $confirmationRequest->getToken())) {
                throw new AccessDeniedHttpException('access denied');
            }

            $response['codeField'] = $confirmationRequest->getCodeField();
            $response['response'] = $this->confirmationService->send($confirmationRequest, $ouuid);

            return new JsonResponse($response);
        } catch (AccessDeniedHttpException $e) {
            $response['emsStatus'] = 403;
            $response['message'] = $e->getMessage();

            return new JsonResponse($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $response['emsStatus'] = 500;
            $response['message'] = $e->getMessage();

            return new JsonResponse($response);
        }
    }
}

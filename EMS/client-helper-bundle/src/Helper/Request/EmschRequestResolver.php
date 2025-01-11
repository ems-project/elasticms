<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class EmschRequestResolver implements ArgumentValueResolverInterface
{
    #[\Override]
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return EmschRequest::class === $argument->getType();
    }

    /**
     * @return iterable<EmschRequest>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield EmschRequest::fromRequest($request);
    }
}

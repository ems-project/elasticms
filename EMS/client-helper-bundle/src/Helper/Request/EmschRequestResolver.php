<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class EmschRequestResolver implements ValueResolverInterface
{
    /**
     * @return iterable<EmschRequest>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (EmschRequest::class !== $argumentType) {
            return [];
        }

        yield EmschRequest::fromRequest($request);
    }
}

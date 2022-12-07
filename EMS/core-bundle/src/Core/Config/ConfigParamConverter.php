<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Config;

use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConfigParamConverter implements ParamConverterInterface
{
    public function __construct(protected readonly StorageManager $storageManager)
    {
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $hash = $request->attributes->getAlnum('hash');
        if (!\is_string($hash)) {
            return false;
        }

        try {
            $options = Json::decode($this->storageManager->getContents($hash));
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }

        $configClass = $configuration->getClass();
        $request->attributes->set($configuration->getName(), new $configClass($options));

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return \is_subclass_of($configuration->getClass(), ConfigInterface::class);
    }
}

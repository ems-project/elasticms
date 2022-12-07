<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CoreBundle\Core\Config\AbstractConfigFactory;
use EMS\CoreBundle\Core\Config\ConfigFactoryInterface;
use EMS\CoreBundle\Service\ContentTypeService;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaLibraryConfigFactory extends AbstractConfigFactory implements ConfigFactoryInterface
{
    public function __construct(private readonly ContentTypeService $contentTypeService)
    {
    }

    /** {@inheritdoc} */
    public function create(array $options): MediaLibraryConfig
    {
        $resolvedOptions = $this->resolveOptions($options);
        $hash = $this->getHash($resolvedOptions);

        $contentType = $this->contentTypeService->giveByName($resolvedOptions['contentTypeName']);

        return new MediaLibraryConfig($hash, $contentType);
    }

    public function createFromHash(string $hash): MediaLibraryConfig
    {
        $options = $this->getOptions($hash);

        return $this->create($options);
    }

    /**
     * @param array<mixed> $options
     *
     * @return array{contentTypeName: string}
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'contentTypeName',
            ]);

        /** @var array{contentTypeName: string} $resolved */
        $resolved = $resolver->resolve($options);

        return $resolved;
    }
}

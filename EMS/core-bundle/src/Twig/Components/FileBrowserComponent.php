<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components;

use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @template TData of array{contentTypeName: string}
 */
final class FileBrowserComponent
{
    public function __construct(private readonly StorageManager $storageManager)
    {
    }

    #[ExposeInTemplate('hash')]
    public string $hash;

    /**
     * @param TData $data
     *
     * @return TData
     */
    #[PreMount]
    public function validate(array $data): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'contentTypeName',
        ]);

        /** @var TData $validated */
        $validated = $resolver->resolve($data);
        $this->hash = $this->storageManager->saveConfig($validated);

        return $validated;
    }
}

<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components;

use EMS\CoreBundle\Core\Component\JsonMenuNested\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\JsonMenuNestedConfigFactory;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

class JsonMenuNestedComponent
{
    public function __construct(
       private readonly JsonMenuNestedConfigFactory $jsonMenuNestedConfigFactory
    ) {
    }

    #[ExposeInTemplate('hash')]
    public string $hash;
    #[ExposeInTemplate('id')]
    public string $id;

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    #[PreMount]
    public function validate(array $options): array
    {
        /** @var JsonMenuNestedConfig $config */
        $config = $this->jsonMenuNestedConfigFactory->createFromOptions($options);

        $this->hash = $config->getHash();
        $this->id = $config->getId();

        return [];
    }
}

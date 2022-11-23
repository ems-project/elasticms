<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Builder;

interface BuilderDocumentInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getContentType(): string;

    /**
     * @return array<mixed>
     */
    public function getDataSource(): array;
}

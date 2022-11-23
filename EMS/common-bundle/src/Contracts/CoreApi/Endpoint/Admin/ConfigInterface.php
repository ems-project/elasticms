<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin;

interface ConfigInterface
{
    public function getType(): string;

    /**
     * @return string[]
     */
    public function index(): array;

    /**
     * @return mixed[]
     */
    public function get(string $name): array;

    /**
     * @param mixed[] $data
     */
    public function update(string $entityName, array $data): string;

    public function delete(string $name): string;

    /**
     * @param mixed[] $data
     */
    public function create(array $data): string;
}

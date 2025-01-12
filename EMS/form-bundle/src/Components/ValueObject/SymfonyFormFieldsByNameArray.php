<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

use EMS\Helpers\Standard\Json;

class SymfonyFormFieldsByNameArray
{
    /** @param mixed[] $fields */
    public function __construct(private readonly array $fields)
    {
    }

    /** @param string[] $exclude */
    public function getFieldIdsJson(array $exclude = []): string
    {
        if (0 === \count($this->fields)) {
            return '';
        }

        try {
            return Json::encode(\array_diff(\array_keys($this->flattenWithKeys($this->fields)), $exclude));
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @param mixed[] $array
     * @param mixed[] $result
     *
     * @return mixed[]
     */
    private function flattenWithKeys(array $array, string $childPrefix = '_', string $root = '', array $result = []): array
    {
        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $result = $this->flattenWithKeys($value, $childPrefix, $root.$key.$childPrefix, $result);
                continue;
            }

            $result[$root.$key] = $value;
        }

        return $result;
    }
}

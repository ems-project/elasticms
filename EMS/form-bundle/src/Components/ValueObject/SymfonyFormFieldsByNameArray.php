<?php

namespace EMS\FormBundle\Components\ValueObject;

class SymfonyFormFieldsByNameArray
{
    /** @var mixed[] */
    private $fields;

    /** @param mixed[] $fields */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /** @param string[] $exclude */
    public function getFieldIdsJson(array $exclude = []): string
    {
        if (0 === \count($this->fields)) {
            return '';
        }

        $json = \json_encode(\array_diff(\array_keys($this->flattenWithKeys($this->fields)), $exclude));

        return false === $json ? '' : $json;
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

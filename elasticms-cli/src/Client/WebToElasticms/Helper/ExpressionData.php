<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Helper;

use EMS\CommonBundle\Elasticsearch\Document\Document;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ExpressionData
{
    private readonly PropertyAccessor $propertyAccessor;

    /**
     * @param array<mixed> $data
     */
    public function __construct(private readonly array $data)
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @return mixed|null
     */
    public function get(string $path)
    {
        $property = Document::fieldPathToPropertyPath($path);

        return $this->propertyAccessor->getValue($this->data, $property);
    }
}

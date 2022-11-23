<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Helper;

use EMS\CommonBundle\Elasticsearch\Document\Document;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ExpressionData
{
    /**
     * @var mixed[]
     */
    private array $data;
    private PropertyAccessor $propertyAccessor;

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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

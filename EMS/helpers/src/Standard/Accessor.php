<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Accessor
{
    public static function fieldPathToPropertyPath(string $fieldPath): string
    {
        $propertyPath = \preg_replace_callback(
            '/(?P<slug>[^\[\.]*)(?P<indexes>(\[.*\])*)\.?/',
            function ($matches) {
                if ('' === $matches['slug']) {
                    return $matches['indexes'];
                }

                return \sprintf('[%s]%s', $matches['slug'], $matches['indexes']);
            },
            $fieldPath
        );
        if (null === $propertyPath) {
            throw new \RuntimeException(\sprintf('Not able to convert the field path %s into a property path', $fieldPath));
        }

        return $propertyPath;
    }
}

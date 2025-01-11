<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Translation;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

final class TranslationFile implements \Countable, \Stringable
{
    public string $resource;
    public string $format;
    public string $locale;

    public function __construct(SplFileInfo $file)
    {
        /** @var array{string, string, string} $fileNameParts */
        $fileNameParts = \explode('.', $file->getFilename());

        $this->format = \array_pop($fileNameParts);
        $this->locale = \array_pop($fileNameParts);
        $this->resource = $file->getPathname();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->resource;
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->toArray());
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->flatten(Yaml::parseFile($this->resource, Yaml::PARSE_CONSTANT));
    }

    /**
     * @param array<string, array<mixed>|string> $messages
     *
     * @return array<string, string>
     */
    private function flatten(array $messages): array
    {
        $result = [];
        foreach ($messages as $key => $value) {
            if (\is_array($value)) {
                foreach ($this->flatten($value) as $k => $v) {
                    $result[$key.'.'.$k] = $v;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Exception\TemplatingException;

final class TemplateName
{
    private readonly string $contentType;
    private readonly string $searchValue;
    private readonly ?string $searchField;

    private const REGEX_MATCH_OUUID = '/^@EMSCH\/(?<content_type>[a-z][a-z0-9\-_]*):(?<search_val>.*)$/';
    private const REGEX_MATCH_NAME = '/^@EMSCH\/(?<content_type>[a-z][a-z0-9\-_]*)\/(?<search_val>.*)$/';

    public function __construct(string $name)
    {
        $match = $this->match($name);
        [$contentType, $searchValue, $searchField] = $match;

        $this->contentType = $contentType;
        $this->searchValue = $searchValue;
        $this->searchField = $searchField;
    }

    public static function validate(string $name): bool
    {
        return TemplateDocument::PREFIX === \substr($name, 0, 6);
    }

    public function getSearchName(): string
    {
        $separator = '_id' === $this->searchField ? ':' : '/';

        return \implode('', [$this->contentType, $separator, $this->searchValue]);
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getSearchValue(): string
    {
        return $this->searchValue;
    }

    public function getSearchField(): ?string
    {
        return $this->searchField;
    }

    /**
     * @return array{string, string, string|null}
     */
    private function match(string $name): array
    {
        if ('@' !== \substr($name, 0, 1)) {
            $name = "@EMSCH/$name";
        }
        \preg_match(self::REGEX_MATCH_OUUID, $name, $matchOuuid);

        if ($matchOuuid) {
            return [$matchOuuid['content_type'], $matchOuuid['search_val'], '_id'];
        }

        \preg_match(self::REGEX_MATCH_NAME, $name, $matchName);

        if ($matchName) {
            return [$matchName['content_type'], $matchName['search_val'], null];
        }

        throw new TemplatingException(\sprintf('Invalid template name: %s', $name));
    }
}

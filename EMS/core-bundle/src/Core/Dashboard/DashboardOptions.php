<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Dashboard;

/**
 * @implements \ArrayAccess<string, bool|string>
 */
class DashboardOptions implements \ArrayAccess
{
    /** @var array<string, bool|string> */
    private array $options = [];

    final public const OBJECT_PICKER = 'object_picker';
    final public const BODY = 'body';
    final public const HEADER = 'header';
    final public const FOOTER = 'footer';

    final public const FILENAME = 'filename';
    final public const MIMETYPE = 'mimetype';
    final public const FILE_DISPOSITION = 'fileDisposition';

    private const OPTIONS = [
        self::OBJECT_PICKER,
        self::BODY,
        self::HEADER,
        self::FOOTER,
        self::FILENAME,
        self::MIMETYPE,
        self::FILE_DISPOSITION,
    ];

    /**
     * @param array<string, bool|string> $data
     */
    public function __construct(array $data)
    {
        foreach (self::OPTIONS as $field) {
            if (isset($data[$field])) {
                $this->options[$field] = $data[$field];
            }
        }
    }

    /**
     * @return array<string, true|string>
     */
    public function getOptions(): array
    {
        return \array_filter($this->options);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset): null|string|bool
    {
        return $this->options[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->options[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->options[$offset]);
    }
}

<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\CommonBundle\Common\Standard\Hash;
use Symfony\Component\Finder\SplFileInfo;

final class TemplateFile
{
    private readonly string $ouuid;
    private readonly string $name;
    private readonly string $path;

    public function __construct(SplFileInfo $file, private readonly string $contentTypeName)
    {
        $this->path = $file->getPathname();

        $pathName = $file->getRelativePathname();
        if ('/' !== \DIRECTORY_SEPARATOR) {
            $pathName = \str_replace(\DIRECTORY_SEPARATOR, '/', $pathName);
        }

        $this->ouuid = Hash::string($contentTypeName.$pathName);
        $this->name = $pathName;
    }

    public function getCode(): string
    {
        if (false === $content = \file_get_contents($this->path)) {
            throw new \RuntimeException(\sprintf('Could not read template code in %s', $this->path));
        }

        return $content;
    }

    public function getContentTypeName(): string
    {
        return $this->contentTypeName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPathName(): string
    {
        return $this->contentTypeName.'/'.$this->name;
    }

    public function getPathOuuid(): string
    {
        return $this->contentTypeName.':'.$this->ouuid;
    }

    public function isFresh(int $time): bool
    {
        return \filemtime($this->path) < $time;
    }
}

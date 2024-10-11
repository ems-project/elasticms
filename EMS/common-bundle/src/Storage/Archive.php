<?php

namespace EMS\CommonBundle\Storage;

class Archive
{
    public static function fromDirectory(string $directory, string $hashAlgo): self
    {
        return new self();
    }
}

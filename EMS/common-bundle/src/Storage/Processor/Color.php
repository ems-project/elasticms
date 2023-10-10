<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

class Color
{
    private int $red;
    private int $green;
    private int $blue;
    private int $alpha;

    public function __construct(string $color)
    {
        $this->red = (int) \hexdec(\substr($color, 1, 2));
        $this->green = (int) \hexdec(\substr($color, 3, 2));
        $this->blue = (int) \hexdec(\substr($color, 5, 2));
        $this->alpha = \intval(\hexdec(\substr($color, 7, 2)) / 2);
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function setRed(int $red): void
    {
        $this->red = $red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function setGreen(int $green): void
    {
        $this->green = $green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    public function setBlue(int $blue): void
    {
        $this->blue = $blue;
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    public function setAlpha(int $alpha): void
    {
        $this->alpha = $alpha;
    }

    public function getColorId(\GdImage $image): int
    {
        $identifier = \imagecolorallocatealpha(
            $image,
            $this->getRed(),
            $this->getGreen(),
            $this->getBlue(),
            $this->getAlpha(),
        );
        if (false === $identifier) {
            throw new \RuntimeException('Unexpected false image color identifier');
        }

        return $identifier;
    }
}

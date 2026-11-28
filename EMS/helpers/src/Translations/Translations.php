<?php

declare(strict_types=1);

namespace EMS\Helpers\Translations;

class Translations
{
    /**
     * @param string[] $locales
     * @param string[] $translations
     */
    public static function fromSimpleArray(array $locales, array $translations, string $defaultLabel): string
    {
        foreach ($locales as $locale) {
            if (isset($translations[$locale])) {
                return $translations[$locale];
            }
        }

        return $defaultLabel;
    }

    /**
     * @param Translation[] $translations
     */
    public function __construct(private readonly array $translations = [])
    {
    }

    /**
     * @param mixed[][] $options
     */
    public static function fromArray(array $options): self
    {
        $translations = [];
        foreach ($options as $key => $value) {
            $translations[$key] = Translation::fromArray($value);
        }

        return new self($translations);
    }

    /**
     * @return Translation[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param string[] $locales
     */
    public function getTranslation(array $locales, string $defaultLabel): Translation
    {
        foreach ($locales as $locale) {
            if (isset($this->translations[$locale])) {
                return $this->translations[$locale];
            }
        }

        return new Translation($defaultLabel);
    }
}

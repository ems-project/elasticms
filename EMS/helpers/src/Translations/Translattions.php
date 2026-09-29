<?php

namespace EMS\Helpers\Translations;

class Translattions
{

    /**
     * @param Translation[] $translations
     */
    public function __construct(private readonly array $translations = [])
    {
    }

    /**
     * @param mixed[][] $options
     * @return self
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
     * @param string $defaultLabel
     * @return Translation
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
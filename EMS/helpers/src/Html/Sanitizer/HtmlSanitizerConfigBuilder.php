<?php

declare(strict_types=1);

namespace EMS\Helpers\Html\Sanitizer;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\UrlAttributeSanitizer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlSanitizerConfigBuilder
{
    /**
     * @var array<mixed>|array{
     *     allow_safe_elements: bool,
     *     allow_attributes: array<string, string|string[]>,
     *     allow_elements: array<string, string|string[]>,
     *     block_elements: string[],
     *     drop_attributes: array<string, string|string[]>,
     *     drop_elements: string[]
     * }
     */
    private array $configSettings;

    /**
     * @var array<mixed>|array{ allow: string[], drop: string[], replace: string[]}
     */
    private readonly array $classes;

    private const array CONFIG_ORDER = [
        'max_input_length',
        'allow_safe_elements',
        'allow_attributes',
        'allow_relative_links',
        'allow_relative_media',
        'allow_elements',
        'block_elements',
        'drop_attributes',
        'drop_elements',
    ];

    /**
     * @param array<mixed> $settings
     */
    public function __construct(array $settings = [])
    {
        $settings = $this->getOptionsResolver()->resolve($settings);
        $this->classes = $settings['classes'];

        foreach (self::CONFIG_ORDER as $setting) {
            $this->configSettings[$setting] = $settings[$setting];
        }
    }

    public function build(): HtmlSanitizerConfig
    {
        $config = new HtmlSanitizerConfig();

        $defaultSanitizers = $config->getAttributeSanitizers();
        foreach ($defaultSanitizers as $sanitizer) {
            if ($sanitizer instanceof UrlAttributeSanitizer) {
                $config = $config->withoutAttributeSanitizer($sanitizer);
            }
        }

        $config = $config
            ->withAttributeSanitizer(new HtmlSanitizerClass($this->classes))
            ->withAttributeSanitizer(new HtmlSanitizerLink());

        foreach ($this->configSettings as $setting => $value) {
            $config = match ($setting) {
                'max_input_length' => $config->withMaxInputLength($value),
                'allow_relative_links' => $config->allowRelativeLinks($value),
                'allow_relative_media' => $config->allowRelativeMedias($value),
                'allow_safe_elements' => true === $value ? $config->allowSafeElements() : $config,
                'allow_attributes' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, array|string $item, string $key) => $config->allowAttribute(
                        attribute: $key,
                        allowedElements: \is_array($item) ? \array_values($item) : $item
                    )
                ),
                'allow_elements' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, array|string $item, string $key) => $config->allowElement(
                        element: $key,
                        allowedAttributes: \is_array($item) ? \array_values($item) : $item
                    )
                ),
                'block_elements' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, string $item) => $config->blockElement($item)
                ),
                'drop_attributes' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, array|string $item, string $key) => $config->dropAttribute(
                        attribute: $key,
                        droppedElements: \is_array($item) ? \array_values($item) : $item
                    )
                ),
                'drop_elements' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, string $item) => $config->dropElement($item)
                ),
                default => throw new \Exception(\sprintf('Unknown settings %s', $setting)),
            };
        }

        return $config;
    }

    /**
     * @param array<int, list<string>|string> $items
     */
    private function eachItem(HtmlSanitizerConfig $config, array $items, callable $callback): HtmlSanitizerConfig
    {
        foreach ($items as $key => $item) {
            $config = $callback($config, $item, $key);
        }

        return $config;
    }

    private function getOptionsResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver
            ->setDefaults([
                'max_input_length' => 500000,
                'allow_safe_elements' => true,
                'allow_relative_links' => true,
                'allow_relative_media' => true,
                'allow_attributes' => ['class' => '*'],
                'allow_elements' => [],
                'block_elements' => [],
                'drop_attributes' => [],
                'drop_elements' => [],
                'classes' => function (OptionsResolver $classesResolver) {
                    $classesResolver->setDefaults([
                        'allow' => [],
                        'drop' => [],
                        'replace' => [],
                    ]);
                },
            ])
            ->setAllowedTypes('allow_safe_elements', 'bool')
            ->setAllowedTypes('max_input_length', 'int')
        ;

        return $optionsResolver;
    }
}

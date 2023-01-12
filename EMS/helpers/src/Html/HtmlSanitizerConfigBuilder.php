<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;
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
    private array $classes;

    private const CONFIG_ORDER = [
        'allow_safe_elements',
        'allow_attributes',
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
        $config = $config->withAttributeSanitizer($this->classSanitizer());

        foreach ($this->configSettings as $setting => $value) {
            $config = match ($setting) {
                'allow_safe_elements' => true === $value ? $config->allowSafeElements() : $config,
                'allow_attributes' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, array|string $item, string $key) => $config->allowAttribute($key, $item)
                ),
                'allow_elements' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, array|string $item, string $key) => $config->allowElement($key, $item)
                ),
                'block_elements' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, string $item) => $config->blockElement($item)
                ),
                'drop_attributes' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, array|string $item, string $key) => $config->dropAttribute($key, $item)
                ),
                'drop_elements' => $this->eachItem($config, $value,
                    fn (HtmlSanitizerConfig $config, string $item) => $config->dropElement($item)
                ),
                default => throw new \Exception(\sprintf('Unknown settings %s', $setting))
            };
        }

        return $config;
    }

    /**
     * @param array<mixed> $items
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
                'allow_safe_elements' => true,
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
        ;

        return $optionsResolver;
    }

    private function classSanitizer(): AttributeSanitizerInterface
    {
        return new class($this->classes) implements AttributeSanitizerInterface {
            /** @param array<mixed>|array{ allow: string[], drop: string[], replace: string[]} $settings */
            public function __construct(private readonly array $settings = [])
            {
            }

            public function getSupportedElements(): ?array
            {
                return null;
            }

            public function getSupportedAttributes(): ?array
            {
                return ['class'];
            }

            public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
            {
                $classes = \explode(' ', $value);
                $classNames = \array_filter($classes, 'trim');

                if (\count($this->settings['allow']) > 0) {
                    $classNames = \array_filter($classNames, fn (string $className) => \in_array($className, $this->settings['allow']));
                }

                if (\count($this->settings['drop']) > 0) {
                    $classNames = \array_filter($classNames, fn (string $className) => !\in_array($className, $this->settings['drop']));
                }

                if (\count($this->settings['replace']) > 0) {
                    $classNames = \array_map(fn (string $className) => $this->settings['replace'][$className] ?? $className, $classNames);
                }

                return \count($classNames) > 0 ? \implode(' ', $classNames) : null;
            }
        };
    }
}

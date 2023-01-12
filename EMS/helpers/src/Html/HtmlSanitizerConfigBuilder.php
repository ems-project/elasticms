<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlSanitizerConfigBuilder
{
    /**
     * @var array<mixed>|array{
     *     allow_safe_elements: bool,
     *     allow_elements: array<int, array{tag: string, attributes: string|string[]}>,
     *     block_elements: string[],
     *     drop_elements: string[]
     * }
     */
    private array $settings;

    /**
     * @param array<mixed> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $this->getOptionsResolver()->resolve($settings);
    }

    public function build(): HtmlSanitizerConfig
    {
        $config = new HtmlSanitizerConfig();

        foreach ($this->settings as $setting => $value) {
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
                'allow_elements' => [],
                'allow_attributes' => [],
                'block_elements' => [],
                'drop_elements' => [],
            ])
            ->setAllowedTypes('allow_safe_elements', 'bool')
        ;

        return $optionsResolver;
    }
}

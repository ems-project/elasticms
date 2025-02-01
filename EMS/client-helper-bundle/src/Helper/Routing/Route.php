<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Helper\Templating\TemplateDocument;
use EMS\Helpers\Standard\Json;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

final class Route
{
    /** @var array<mixed> */
    private array $options;

    /**
     * @param array<mixed> $options
     */
    private function __construct(private readonly string $name, array $options)
    {
        $this->options = $this->resolveOptions($options);
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromData(string $name, array $data): self
    {
        $config = isset($data['config']) ? Json::decode($data['config']) : [];

        if (isset($data['template_static'])) {
            $template = TemplateDocument::PREFIX.'/'.$data['template_static'];
        } else {
            $template = $data['template_source'] ?? null;
        }

        return new self($name, \array_filter(\array_merge($config, [
            'query' => isset($data['query']) ? Json::decode($data['query']) : null,
            'index_regex' => $data['index_regex'] ?? null,
            'template' => $template,
        ])));
    }

    /**
     * @param string[] $locales
     */
    public function addToCollection(RouteCollection $collection, array $locales = [], ?string $prefix = null): void
    {
        $path = $this->options['path'];

        if (null !== $prefix) {
            $this->options['prefix'] = $prefix;
        }

        if (\is_array($path)) {
            foreach ($path as $key => $p) {
                $locale = \in_array($key, $locales) ? (string) $key : null;
                $route = $this->createRoute($p, $locale);
                $collection->add(\sprintf('%s.%s', $this->name, $key), $route);
            }
        } else {
            $collection->add($this->name, $this->createRoute($path));
        }
    }

    private function createRoute(string $path, ?string $locale = null): SymfonyRoute
    {
        $defaults = $this->options['defaults'];

        if ($locale) {
            $defaults['_locale'] = $locale;
        }

        if (null !== $this->options['prefix']) {
            if (!\str_starts_with($path, '/')) {
                $path = $this->options['prefix'].'/'.$path;
            } else {
                $path = $this->options['prefix'].$path;
            }
        }

        return new SymfonyRoute(
            $path,
            $defaults,
            $this->options['requirements'],
            $this->options['options'],
            $this->options['host'],
            $this->options['schemes'],
            $this->options['method'],
            $this->options['condition']
        );
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['path'])
            ->setDefaults([
                'method' => 'GET',
                'controller' => 'emsch.controller.router::handle',
                'defaults' => [],
                'requirements' => [],
                'options' => [],
                'host' => '',
                'schemes' => [],
                'prefix' => null,
                'type' => null,
                'query' => null,
                'template' => '[template]',
                'index_regex' => null,
                'condition' => '',
            ])
            ->addAllowedTypes('method', ['string', 'string[]'])
            ->setNormalizer('defaults', function (Options $options, $value) {
                if (!isset($value['_controller'])) {
                    $value['_controller'] = $options['controller'];
                }

                return $value;
            })
            ->setNormalizer('options', function (Options $options, $value) {
                if (null !== $options['query']) {
                    $value['query'] = Json::encode($options['query']);
                }

                $value['type'] = $options['type'];
                $value['template'] = $options['template'];
                $value['index_regex'] = $options['index_regex'];

                return $value;
            })
            ->setNormalizer('method', function (Options $options, $value) {
                if (\is_string($value)) {
                    return [$value];
                }

                return $value;
            })
        ;

        return $resolver->resolve($options);
    }
}

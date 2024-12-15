<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Helper\Templating\TemplateFiles;
use EMS\ClientHelperBundle\Helper\Templating\TemplateName;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class RoutingFile implements \Countable
{
    /** @var array<string, mixed> */
    private array $routes = [];

    private const FILE_NAME = 'routes.yaml';

    public function __construct(string $directory, private readonly TemplateFiles $templateFiles)
    {
        $file = $directory.\DIRECTORY_SEPARATOR.self::FILE_NAME;
        $content = \file_exists($file) ? (\file_get_contents($file) ?: '') : false;
        /** @var array<string, mixed> $routes */
        $routes = \file_exists($file) && $content ? Yaml::parse($content) : [];

        foreach ($routes as $name => $data) {
            if (isset($data['config'])) {
                $data['config'] = Json::encode($data['config']);
            }

            $data['name'] = $name;
            $this->routes[$name] = $data;
        }
    }

    /**
     * @param RoutingDocument[] $documents
     */
    public static function build(string $directory, TemplateFiles $templateFiles, iterable $documents): self
    {
        $routes = [];

        foreach ($documents as $document) {
            $data = $document->getDataSource();
            if (isset($data['template_static'])) {
                $templateFile = $templateFiles->find(new TemplateName($data['template_static']));
                $data['template_static'] = $templateFile ? $templateFile->getPathName() : $data['template_static'];
            }

            if (isset($data['config'])) {
                $data['config'] = Json::decode($data['config']);
            }

            unset($data['name']);
            unset($data['order']);
            $routes[$document->getName()] = $data;
        }

        $fileName = $directory.\DIRECTORY_SEPARATOR.self::FILE_NAME;
        $fs = new Filesystem();
        $fs->dumpFile($fileName, $routes ? Yaml::dump($routes, 3) : '');

        return new self($directory, $templateFiles);
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        $order = 0;
        $data = [];

        foreach ($this->routes as $name => $route) {
            if (isset($route['template_static'])) {
                $template = $this->templateFiles->find(new TemplateName($route['template_static']));
                if ($template) {
                    $route['template_static'] = $template->getPathOuuid();
                }
            }

            $route['order'] = ++$order;
            $data[$name] = $route;
        }

        return $data;
    }

    public function count(): int
    {
        return \count($this->routes);
    }

    /**
     * @return Route[]
     */
    public function createRoutes(): array
    {
        $routes = [];

        foreach ($this->routes as $name => $data) {
            if (isset($data['template_static'])) {
                $template = $this->templateFiles->find(new TemplateName($data['template_static']));
                $data['template_static'] = $template ? $template->getPathName() : $data['template_static'];
            }

            $routes[] = Route::fromData($name, $data);
        }

        return $routes;
    }
}

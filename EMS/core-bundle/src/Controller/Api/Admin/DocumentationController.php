<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Api\Admin;

use phpDocumentor\Reflection\Types\False_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class DocumentationController extends AbstractController
{
    public function __construct(private readonly string $templateNamespace,
                                private readonly RouterInterface $router)
    {
    }
    public function getDocumentation(Request $request): Response
    {
        $format = $request->getRequestFormat();

        if ($format === 'json') {
            $paths = [];
            $tags = [];

            foreach ($this->getRoutes() as $name => $route) {
                $path = $route->getPath();
                $methods = $route->getMethods();
                $controller = $route->getDefault('_controller') ?? 'Not defined';

                $tag = 'Default';
                if (str_contains($controller, 'Controller')) {
                    $parts = explode('\\', $controller);
                    $controllerName = end($parts);
                    $tag = str_replace('Controller', '', explode('::', $controllerName)[0]);
                }

                $tags[$tag] = [
                    'name' => $tag,
                    'description' => "Endpoints related to $tag"
                ];

                foreach ($methods as $method) {
                    $paths[$path][strtolower($method)] = [
                        'tags' => [$tag],
                        'summary' => $name,
                        'responses' => [
                            '200' => [
                                'description' => "Response for route $name",
                            ],
                        ],
                    ];
                }
            }

            $openApi = [
                'openapi' => '3.0.0',
                'info' => [
                    'title' => 'Dynamically Generated API',
                    'description' => 'OpenAPI documentation based on Symfony routes',
                    'version' => '1.0.0',
                ],
                'tags' => array_values($tags), 
                'paths' => $paths,
            ];

            return new JsonResponse($openApi);
        }

        return $this->render("@$this->templateNamespace/api/documentation.html.twig");
    }



    public function getRoutes(): array
    {
        $routes = $this->router->getRouteCollection()->all();
        
        return \array_filter($routes, static fn (Route $route) => $route->getOption('openapi') );
    }
}

<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Api\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            foreach ($this->getRoutes() as $name => $route) {
                if (!str_starts_with($name, 'emsco_admin')) {
                    continue;
                }

                $path = $route->getPath();
                $methods = $route->getMethods();
                $controller = $route->getDefault('_controller') ?? 'Not defined';

                foreach ($methods as $method) {
                    $paths[$path][strtolower($method)] = [
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
                'paths' => $paths,
            ];

            return new JsonResponse($openApi);
        }

        return $this->render("@$this->templateNamespace/api/documentation.html.twig");
    }


    public function getRoutes(): array
    {
        $allRoutes = $this->router->getRouteCollection()->all();

        $Routes = [];

        foreach ($allRoutes as $name => $route) {
            if (str_starts_with($name, 'emsco_admin')) {
                $Routes[$name] = $route;
            }
        }

        return $Routes;
    }
}

<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Api\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentationController extends AbstractController
{
    public function __construct(private readonly string $templateNamespace)
    {
    }
    public function getDocumentation(Request $request): Response
    {
        $format = $request->getRequestformat();
        
        if ($format === 'json') {
            return new JsonResponse([]);
        }
        return $this->render("@$this->templateNamespace/api/documentation.html.twig");
    }
}

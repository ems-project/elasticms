<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    use CoreControllerTrait;

    public function documentation(): Response
    {
        return $this->render('@EMSCore/default/documentation.html.twig');
    }
}

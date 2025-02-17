<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\User;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\Core\Form\FieldTypeManager;
use EMS\CoreBundle\Core\Form\FormManager;
use EMS\CoreBundle\Core\User\GroupManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends AbstractController
{
    public function __construct(
        private readonly LocalizedLoggerInterface $logger,
        private readonly GroupManager $groupManager,
        private readonly FieldTypeManager $fieldTypeManager,
        private readonly DataTableFactory $dataTableFactory,
        private readonly string $templateNamespace,
    ) {
    }

    public function index(Request $request): Response
    {
        $test = $this->groupManager->getAll();
        dump($test);
        return $this->render("@$this->templateNamespace/group/overview.html.twig", ['test' => $test]);
    }
}

<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\User;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\Core\Form\FieldTypeManager;
use EMS\CoreBundle\Core\User\GroupManager;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Entity\User;
use EMS\CoreBundle\Form\Form\GroupType;
use EMS\CoreBundle\Form\Form\UserType;
use EMS\CoreBundle\Routes;
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
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['mode' => UserType::MODE_CREATE]);
        $form->handleRequest($request);

        return $this->render("@$this->templateNamespace/group/overview.html.twig", [
            'test' => $test,
            'form' => $form,
        ]);
    }

    public function addGroup(Request $request): Response
    {
        $group = new Group();

        $form = $this->createForm(GroupType::class, $group, ['mode' => UserType::MODE_CREATE]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupManager->create($group);
            //            $continue = $this->userExist($group, 'add');
            //
            //            if ($continue) {
            //                $group->setEnabled(true);
            //                $this->groupManager->update($group);
            //                $this->addFlash('notice', 'User created!');
            //
            //                return $this->redirectToRoute(Routes::USER_INDEX);
            //            }
        }

        return $this->render("@$this->templateNamespace/group/create.html.twig", [
            'form' => $form,
        ]);
    }
}

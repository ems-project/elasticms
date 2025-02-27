<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\User;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use EMS\CoreBundle\Controller\CoreControllerTrait;
use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\Core\Form\FieldTypeManager;
use EMS\CoreBundle\Core\UI\Page\Navigation;
use EMS\CoreBundle\Core\User\GroupManager;
use EMS\CoreBundle\DataTable\Type\GroupDataTableType;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Entity\User;
use EMS\CoreBundle\Form\Form\GroupType;
use EMS\CoreBundle\Form\Form\TableType;
use EMS\CoreBundle\Form\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

class GroupController extends AbstractController
{
    use CoreControllerTrait;

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
        $table = $this->dataTableFactory->create(GroupDataTableType::class);
        $test = $this->groupManager->getAll();

        $form = $this->createForm(TableType::class, $table, [
            'reorder_label' => t('type.reorder', ['type' => 'form'], 'emsco-core'),
        ]);
        $form->handleRequest($request);
        if ($this->getClickedButtonName($form)) {
            \dump($this->getClickedButtonName($form));

            return $this->render("@$this->templateNamespace/group/create.html.twig", [
                'test' => $test,
                'form' => $form,
            ]);
        }

        return $this->render("@$this->templateNamespace/crud/overview.html.twig", [
            'test' => $test,
            'form' => $form,
            'title' => t('type.title_overview', ['type' => 'group'], 'emsco-core'),
            'subTitle' => t('type.title_sub', ['type' => 'group'], 'emsco-core'),
            'breadcrumb' => $this->breadcrumb(),
        ]);
    }

    public function addGroup(Request $request): Response
    {
        $group = new Group();

        $form = $this->createForm(GroupType::class, $group, ['mode' => UserType::MODE_CREATE]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //            dump($this->getClickedButtonName($form));
            //            if (GroupType::DELETE_BUTTON === $this->getClickedButtonName($form)) {
            //                dump('c un delete');
            //            }
            $this->groupManager->create($group);

            //            $continue = $this->userExist($group, 'add');
            //
            //            if ($continue) {
            //                $group->setEnabled(true);
            //                $this->groupManager->update($group);
            //                $this->addFlash('notice', 'User created!');
            //
            return $this->redirectToRoute('emsco_group_admin_index');
            //            }
        }

        return $this->render("@$this->templateNamespace/group/create.html.twig", [
            'form' => $form,
        ]);
    }

    public function deleteGroup(Group $group): Response
    {
        $this->groupManager->deleteGroup($group);

        return $this->redirectToRoute('emsco_group_admin_index');
    }

    private function breadcrumb(): Navigation
    {
        return Navigation::admin()->add(
            label: t('key.users', [], 'emsco-core'),
            icon: 'fa fa-user',
            route: 'emsco_user_index'
        )->add(
            label: t('key.groups', [], 'emsco-core'),
            icon: 'fa fa-users',
            route: 'emsco_group_admin_index'
        );
    }
}

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
use EMS\CoreBundle\DataTable\Type\UserDataTableType;
use EMS\CoreBundle\DataTable\Type\UserGroupDataTableType;
use EMS\CoreBundle\DataTable\Type\Wysiwyg\WysiwygStylesSetDataTableType;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Form\GroupType;
use EMS\CoreBundle\Form\Form\TableType;
use EMS\CoreBundle\Form\Form\UserType;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

class GroupController extends AbstractController
{
    use CoreControllerTrait;

    public function __construct(
        private readonly LocalizedLoggerInterface $logger,
        private readonly GroupManager $groupManager,
        private readonly UserService $userService,
        private readonly FieldTypeManager $fieldTypeManager,
        private readonly DataTableFactory $dataTableFactory,
        private readonly string $templateNamespace,
    ) {
    }

    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create(GroupDataTableType::class);
        $list_user_group = $this->groupManager->getAll();

        $form = $this->createForm(TableType::class, $table, [
            'reorder_label' => t('type.reorder', ['type' => 'form'], 'emsco-core'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            match ($this->getClickedButtonName($form)) {
                TableAbstract::DELETE_ACTION => $this->groupManager->deleteByIds($table->getSelected()),
                default => $this->logger->messageError(t('log.error.invalid_table_action', [], 'emsco-core')),
            };

            return $this->redirectToRoute(Routes::GROUP_INDEX);
        }

        return $this->render("@$this->templateNamespace/crud/overview.html.twig", [
            'list_user_group' => $list_user_group,
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
            $this->groupManager->create($group);

            return $this->redirectToRoute('emsco_group_admin_index');
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

    public function deleteSelectedGroup($SelectedGroup): Response
    {
        // TableAbstract::DELETE_ACTION => $this->deleteSelectedGroup($SelectedGroup);
        foreach ($SelectedGroup as $group) {
            $this->groupManager->deleteGroup($group);
        }

        return $this->redirectToRoute('emsco_group_admin_index');
    }

    public function deleteAllGroup(): Response
    {
        $this->groupManager->deleteAllGroup();

        return $this->redirectToRoute('emsco_group_admin_index');
    }

    public function editGroup(Group $group, Request $request): Response
    {
        $userGroupDataTable = $this->usersInGroupDataTable($request, $group);
        $this->groupManager->editGroup($group);
        $form = $this->createForm(GroupType::class, $group, [
            'mode' => UserType::MODE_UPDATE,
        ]);
        $form->handleRequest($request);
//        $users = $this->userService->getUsersByGroup($group->getName());
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupManager->editGroup($group);

            return $this->redirectToRoute('emsco_group_admin_index');
        }

        return $this->render("@$this->templateNamespace/group/create.html.twig", [
            'form' => $form,
            'datatableForm' => $userGroupDataTable->createView(),
            'title' => t('type.title_edit', ['type' => 'group'], 'emsco-core'),
            'subTitle' => t('type.title_sub', ['type' => 'group'], 'emsco-core'),
            'breadcrumb' => $this->breadcrumb(),
        ]);
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

    /**
     * @return RedirectResponse|FormInterface<mixed>
     */
    private function usersInGroupDataTable(Request $request, Group $group): RedirectResponse|FormInterface
    {

        $table = $this->dataTableFactory->create(UserDataTableType::class, [
            'light' => true,
            'group' => $group,
        ]);

        $form = $this->createForm(TableType::class, $table);
//        $table = $this->dataTableFactory->create(UserGroupDataTableType::class);
//        $form = $this->createForm( TableType::class, $table, [
//            'reorder_label' => t('type.reorder', ['type' => 'form'], 'emsco-core'),
//        ]);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            // ...
//            dump($form->getData());
//        }

        return $form;
        
    }
}

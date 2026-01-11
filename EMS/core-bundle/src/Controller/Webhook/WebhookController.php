<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Webhook;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use EMS\CoreBundle\Controller\CoreControllerTrait;
use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\Core\UI\Page\Navigation;
use EMS\CoreBundle\Core\UI\Page\Page;
use EMS\CoreBundle\Core\User\GroupManager;
use EMS\CoreBundle\Routes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\Translation\t;

class WebhookController extends AbstractController
{
    use CoreControllerTrait;

    public function __construct(
        private readonly LocalizedLoggerInterface $logger,
        private readonly GroupManager $groupManager,
        private readonly DataTableFactory $dataTableFactory,
        private readonly string $templateNamespace,
    ) {
    }

    public function index(Request $request): Page|RedirectResponse
    {
        //        $table = $this->dataTableFactory->create(GroupDataTableType::class);
        //
        //        $form = $this->createForm(TableType::class, $table, [
        //            'reorder_label' => t('type.reorder', ['type' => 'group'], 'emsco-core'),
        //        ]);
        //        $form->handleRequest($request);
        //        if ($form->isSubmitted() && $form->isValid()) {
        //            match ($this->getClickedButtonName($form)) {
        //                TableAbstract::DELETE_ACTION => $this->groupManager->deleteByIds($table->getSelected()),
        //                default => $this->logger->messageError(t('log.error.invalid_table_action', [], 'emsco-core')),
        //            };
        //
        //            return $this->redirectToRoute(Routes::GROUP_INDEX);
        //        }

        return new Page([
            //            'datatable' => ['form' => $form->createView()],
            'title' => t('type.title_overview', ['type' => 'webhook'], 'emsco-core'),
            'subTitle' => t('type.title_sub', ['type' => 'webhook'], 'emsco-core'),
            'breadcrumb' => $this->breadcrumb(),
        ]);
    }

    private function breadcrumb(): Navigation
    {
        return Navigation::admin()->add(
            label: t('key.webhooks', [], 'emsco-core'),
            icon: 'fa fa-chain',
            route: Routes::WEBHOOK_INDEX,
        );
    }
}

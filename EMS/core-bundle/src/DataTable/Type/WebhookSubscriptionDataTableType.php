<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\Webhook\WebhookSubscriptionManager;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Routes;

use function Symfony\Component\Translation\t;

class WebhookSubscriptionDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;

    public function __construct(WebhookSubscriptionManager $entityService)
    {
        parent::__construct($entityService);
    }

    #[\Override]
    public function build(EntityTable $table): void
    {
        $table->addColumn(t('field.id', [], 'emsco-core'), 'name');
        $this
            ->addColumnsCreatedModifiedDate($table)
            ->addTableActionDelete($table, 'webhook_subscription')
            ->addItemDelete($table, 'webhook_subscription', Routes::WEBHOOK_SUBSCRIPTION_DELETE);
    }
}

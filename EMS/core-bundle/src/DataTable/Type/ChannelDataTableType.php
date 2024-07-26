<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Entity\Channel;
use EMS\CoreBundle\Form\Data\BoolTableColumn;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\Channel\ChannelService;

use function Symfony\Component\Translation\t;

class ChannelDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;

    public function __construct(ChannelService $entityService)
    {
        parent::__construct($entityService);
    }

    public function build(EntityTable $table): void
    {
        $this->addColumnsOrderLabelName($table);
        $table->getColumnByName('name')?->setPathCallback(
            pathCallback: fn (Channel $channel, string $baseUrl = '') => $baseUrl.$channel->getEntryPath(),
            target: '_blank'
        );

        $table->addColumn(t('field.alias', [], 'emsco-core'), 'alias');
        $table->addColumnDefinition(new BoolTableColumn(t('field.public_access', [], 'emsco-core'), 'public'));

        $table->addItemGetAction(
            route: 'ems_core_channel_edit',
            labelKey: t('action.edit', [], 'emsco-core'),
            icon: 'pencil'
        );
        $table->addItemPostAction(
            route: 'ems_core_channel_delete',
            labelKey: t('action.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('type.delete_confirm', ['type' => 'channel'], 'emsco-core')
        )->setButtonType('outline-danger');

        $this
            ->addTableToolbarActionAdd($table, 'ems_core_channel_add')
            ->addTableActionDelete($table, 'channel');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}

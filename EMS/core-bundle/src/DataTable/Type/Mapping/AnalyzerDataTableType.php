<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\Mapping;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\Mapping\AnalyzerManager;
use EMS\CoreBundle\DataTable\Type\DataTableTypeTrait;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Routes;

use function Symfony\Component\Translation\t;

class AnalyzerDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;

    public function __construct(AnalyzerManager $analyzerManager)
    {
        parent::__construct($analyzerManager);
    }

    public function build(EntityTable $table): void
    {
        $this->addColumnsOrderLabelName($table);

        $table->addItemGetAction(
            route: Routes::ANALYZER_EDIT,
            labelKey: t('action.edit', [], 'emsco-core'),
            icon: 'pencil'
        );
        $table->addItemGetAction(
            route: Routes::ANALYZER_EXPORT,
            labelKey: t('action.export', [], 'emsco-core'),
            icon: 'sign-out'
        );
        $table->addItemPostAction(
            route: Routes::ANALYZER_DELETE,
            labelKey: t('action.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('type.delete_confirm', ['type' => 'analyzer'], 'emsco-core')
        )->setButtonType('outline-danger');

        $table->addToolbarAction(
            label: t('action.add', [], 'emsco-core'),
            icon: 'fa fa-plus',
            routeName: Routes::ANALYZER_ADD
        );

        $this->addTableActionDelete($table, 'analyzer');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}

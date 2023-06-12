<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\DataTable;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\DataTable\Type\DataTableTypeCollection;
use EMS\CoreBundle\Core\DataTable\Type\DataTableTypeInterface;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Routes;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class DataTableFactory
{
    public function __construct(
        private readonly DataTableTypeCollection $typeCollection,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security
    ) {
    }

    public function create(string $class): TableAbstract
    {
        $type = $this->typeCollection->getByClass($class);

        return $this->build($type);
    }

    public function createFromHash(string $hash): TableAbstract
    {
        $type = $this->typeCollection->getByHash($hash);

        return $this->build($type);
    }

    private function checkRoles(DataTableTypeInterface $type): void
    {
        $roles = $type->getRoles();
        $grantedRoles = \array_filter($roles, fn (string $role) => $this->security->isGranted($role));

        if (0 === \count($grantedRoles)) {
            throw new AccessDeniedException();
        }
    }

    private function build(DataTableTypeInterface $type): TableAbstract
    {
        $this->checkRoles($type);
        $ajaxUrl = $this->generateAjaxUrl($type);

        return match (true) {
            $type instanceof AbstractEntityTableType => $this->buildEntityTable($type, $ajaxUrl),
            default => throw new \RuntimeException('Unknown dataTableType')
        };
    }

    private function buildEntityTable(AbstractEntityTableType $type, string $ajaxUrl): EntityTable
    {
        $table = new EntityTable($type->getEntityService(), $ajaxUrl);
        $type->build($table);

        return $table;
    }

    private function generateAjaxUrl(DataTableTypeInterface $type): string
    {
        return $this->urlGenerator->generate(Routes::DATA_TABLE_AJAX_TABLE, [
            'hash' => $type->getHash(),
        ]);
    }
}

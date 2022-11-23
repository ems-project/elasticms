<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use EMS\CommonBundle\Common\Standard\DateTime;
use EMS\SubmissionBundle\Dto\FormSubmissionsCountDto;
use EMS\SubmissionBundle\Entity\FormSubmission;

/**
 * @extends ServiceEntityRepository<FormSubmission>
 */
class FormSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, FormSubmission::class);
    }

    /**
     * @return array<int, array{name: string, instance: string, total: int, unprocessed_total: int, errors_total: int}>
     */
    public function getMetrics(): array
    {
        $dateFormat = $this->getEntityManager()->getConnection()->getDatabasePlatform()->getDateTimeFormatString();
        $interval4hours = DateTime::create('now - 4 hours');

        $qb = $this->createQueryBuilder('fs');
        $qb
            ->select('fs.name')
            ->addSelect('fs.instance')
            ->addSelect('count(fs.id) as total')
            ->addSelect('sum(case when fs.processId is null then 1 else 0 end) as unprocessed_total')
            ->addSelect('sum(case when (fs.processTryCounter > 2  or fs.created < :created)'.
                ' and fs.processId is null then 1 else 0 end) as errors_total')
            ->groupBy('fs.name, fs.instance')
            ->setParameter('created', $interval4hours->format($dateFormat));

        return $qb->getQuery()->getResult();
    }

    public function findById(string $id): ?FormSubmission
    {
        try {
            $qb = $this->createQueryBuilder('fs');
            $qb
                ->andWhere($qb->expr()->eq('fs.id', ':id'))
                ->setParameter('id', $id);

            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCounts(string $name, string $period, ?string $instance): FormSubmissionsCountDto
    {
        $qb = $this->createCountQueryBuilder($name, $instance);

        $countDto = new FormSubmissionsCountDto($period);
        $countDto->setWaiting($this->countWaiting(clone $qb));
        $countDto->setFailed($this->countFailed(clone $qb));
        $countDto->setProcessed($this->countProcessed(clone $qb));
        $countDto->setTotal($this->countTotal(clone $qb));

        $this->setPeriodCounts($countDto, clone $qb);

        return $countDto;
    }

    private function setPeriodCounts(FormSubmissionsCountDto $countDto, QueryBuilder $qb): void
    {
        $now = new \DateTime('now');
        $startDate = $now->modify(\sprintf('-%s', $countDto->period));
        $qb
            ->andWhere($qb->expr()->gte('fs.created', ':start_datetime'))
            ->setParameter('start_datetime', $startDate);

        $countDto->setPeriodWaiting($this->countWaiting(clone $qb));
        $countDto->setPeriodFailed($this->countFailed(clone $qb));
        $countDto->setPeriodProcessed($this->countProcessed(clone $qb));
        $countDto->setPeriodTotal($this->countTotal(clone $qb));
    }

    private function countFailed(QueryBuilder $qb): int
    {
        $date = new \DateTime('now');
        $date->modify('-4 hour');

        $qb->andWhere($qb->expr()->isNull('fs.processId'));
        $qb->andWhere('(fs.processTryCounter > 0 OR fs.created < :date)');
        $qb->setParameter(':date', $date);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countProcessed(QueryBuilder $qb): int
    {
        $qb->andWhere($qb->expr()->isNotNull('fs.processId'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countWaiting(QueryBuilder $qb): int
    {
        $qb
            ->andWhere($qb->expr()->isNull('fs.processId'))
            ->andWhere($qb->expr()->eq('fs.processTryCounter', 0));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countTotal(QueryBuilder $qb): int
    {
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function createCountQueryBuilder(string $name, ?string $instance): QueryBuilder
    {
        $qb = $this->createQueryBuilder('fs');
        $qb
            ->select('count(fs.id)')
            ->andWhere($qb->expr()->eq('fs.name', ':name'))
            ->setParameter('name', $name);

        if (null !== $instance) {
            $qb
                ->andWhere($qb->expr()->eq('fs.instance', ':instance'))
                ->setParameter('instance', $instance);
        }

        return $qb;
    }
}

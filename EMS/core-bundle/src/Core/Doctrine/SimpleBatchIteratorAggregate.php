<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Doctrine;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use IteratorAggregate;

use function key;

/**
 * @template TKey
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class SimpleBatchIteratorAggregate implements \IteratorAggregate
{
    /** @var iterable<TKey, TValue> */
    private iterable $resultSet;
    private EntityManagerInterface $entityManager;
    /** @psalm-var positive-int */
    private int $batchSize;

    /** @psalm-param positive-int $batchSize */
    public static function fromQuery(AbstractQuery $query, int $batchSize): self
    {
        return new self($query->toIterable(), $query->getEntityManager(), $batchSize);
    }

    /**
     * @param array<C, D> $results
     * @psalm-param positive-int $batchSize
     *
     * @return self<C, D>
     *
     * @template C
     * @template D
     */
    public static function fromArrayResult(array $results, EntityManagerInterface $entityManager, int $batchSize): self
    {
        return new self($results, $entityManager, $batchSize);
    }

    /**
     * @return \Traversable<TKey, TValue>
     *
     * @psalm-suppress InvalidReturnType psalm can't infer the correct key/value pairs here, but we've carefully
     *                                   tested this signature.
     */
    public function getIterator(): \Traversable
    {
        $iteration = 0;

        $this->entityManager->beginTransaction();

        try {
            foreach ($this->resultSet as $key => $value) {
                ++$iteration;

                if (\is_array($value)) {
                    $firstKey = \key($value);
                    if (null !== $firstKey && \is_object($value[$firstKey]) && $value === [$firstKey => $value[$firstKey]]) {
                        yield $key => $this->reFetchObject($value[$firstKey]);

                        $this->flushAndClearBatch($iteration);
                        continue;
                    }
                }

                if (!\is_object($value)) {
                    yield $key => $value;

                    $this->flushAndClearBatch($iteration);
                    continue;
                }

                yield $key => $this->reFetchObject($value);

                $this->flushAndClearBatch($iteration);
            }
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }

        $this->flushAndClearEntityManager();
        $this->entityManager->commit();
    }

    /**
     * BatchIteratorAggregate constructor (private by design: use a named constructor instead).
     *
     * @param iterable<TKey, TValue> $resultSet
     * @psalm-param positive-int $batchSize
     */
    private function __construct(iterable $resultSet, EntityManagerInterface $entityManager, int $batchSize)
    {
        $this->resultSet = $resultSet;
        $this->entityManager = $entityManager;
        $this->batchSize = $batchSize;
    }

    /**
     * @psalm-param TReFetched $object
     *
     * @psalm-return TReFetched
     *
     * @template TReFetched of object
     */
    private function reFetchObject(object $object): object
    {
        $className = \get_class($object);
        $metadata = $this->entityManager->getClassMetadata($className);
        $freshValue = $this->entityManager->find($className, $metadata->getIdentifierValues($object));

        if (!$freshValue) {
            throw new \UnexpectedValueException(\sprintf('Requested batch item %s, hash %s (of type %s) with the identifier "%s" could not be found', \get_class($object), \spl_object_hash($object), $metadata->getName(), \json_encode($metadata->getIdentifierValues($object), JSON_THROW_ON_ERROR)));
        }

        return $freshValue;
    }

    private function flushAndClearBatch(int $iteration): void
    {
        if ($iteration % $this->batchSize) {
            return;
        }

        $this->flushAndClearEntityManager();
    }

    private function flushAndClearEntityManager(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}

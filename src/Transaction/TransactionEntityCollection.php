<?php declare(strict_types=1);

namespace AlexRyall\RewardPoints\Transaction;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(TransactionEntity $entity)
 * @method void              set(string $key, TransactionEntity $entity)
 * @method TransactionEntity[]    getIterator()
 * @method TransactionEntity[]    getElements()
 * @method TransactionEntity|null get(string $key)
 * @method TransactionEntity|null first()
 * @method TransactionEntity|null last()
 */
class TransactionEntityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TransactionEntity::class;
    }
}

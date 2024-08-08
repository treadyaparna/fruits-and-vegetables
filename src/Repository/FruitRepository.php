<?php

namespace App\Repository;

use App\Entity\Fruit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Fruit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fruit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fruit[]    findAll()
 * @method Fruit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FruitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fruit::class);
    }

    /**
     * Add a new Fruit to the database.
     *
     * @param string $name
     * @param int $quantity
     */
    public function add($item): bool
    {
        try {
            $entityManager = $this->getEntityManager();

            $vegetable = new Fruit();
            $vegetable->setName($item['name']);
            $vegetable->setQuantity($item['quantity']);

            $entityManager->persist($vegetable);
            $entityManager->flush();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update existing Fruit to the database.
     *
     * @param Fruit $fruit
     * @return void
     */
    public function update(Fruit $fruit): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($fruit);
        $entityManager->flush();
    }

    /**
     * Delete existing Fruit to the database.
     *
     * @param Fruit $fruit
     * @return bool
     */
    public function delete(Fruit $fruit): bool
    {
        try {
            $entityManager = $this->getEntityManager();

            $entityManager->remove($fruit);

            $entityManager->flush();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * search and find fruits by name
     *
     * @param string $name
     * @return array
     */
    public function findByName(string $name): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->where($qb->expr()->like('f.name', ':name'))
            ->setParameter('name', '%' . $name . '%');
        return $qb->getQuery()->getResult();
    }
}

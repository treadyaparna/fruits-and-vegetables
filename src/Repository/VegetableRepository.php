<?php

namespace App\Repository;

use App\Entity\Vegetable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Vegetable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vegetable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vegetable[]    findAll()
 * @method Vegetable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VegetableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vegetable::class);
    }

    /**
     * Add a new Vegetable to the database.
     *
     * @param string $name
     * @param int $quantity
     */
    public function add($item): bool
    {
        try {
            $entityManager = $this->getEntityManager();

            $vegetable = new Vegetable();
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
     * Update existing Vegetable to the database.
     *
     * @param Vegetable $vegetable
     * @return void
     */
    public function update(Vegetable $vegetable): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($vegetable);
        $entityManager->flush();
    }

    /**
     * Delete existing Vegetable to the database.
     *
     * @param Vegetable $vegetable
     * @return bool
     */
    public function delete(Vegetable $vegetable): bool
    {
        try {
            $entityManager = $this->getEntityManager();

            $entityManager->remove($vegetable);

            $entityManager->flush();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * search and find vegetables by name
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

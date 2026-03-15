<?php

namespace App\Repository;

use App\Entity\RentReceipt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RentReceiptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentReceipt::class);
    }

    /**
     * Compte les quittances d'un locataire pour un mois donné (YYYYMM).
     */
    public function countByTenantAndMonth(int $tenantId, string $yearMonth): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.tenant = :tenant')
            ->andWhere('r.number LIKE :pattern')
            ->setParameter('tenant', $tenantId)
            ->setParameter('pattern', 'QUI-' . $yearMonth . '-%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Génère le prochain numéro de quittance pour un locataire et une période.
     * Format : QUI-YYYYMM-{tenantId}-{seq:02d}
     */
    public function generateNumber(int $tenantId, \DateTimeInterface $periodStart): string
    {
        $yearMonth = $periodStart->format('Ym');
        $seq = $this->countByTenantAndMonth($tenantId, $yearMonth) + 1;

        return sprintf('QUI-%s-%d-%02d', $yearMonth, $tenantId, $seq);
    }
}
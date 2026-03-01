<?php

namespace App\Model\Admin;

use App\Entity\Donation;
use App\Entity\Params;
use App\Model\DonateModel;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TreasurerModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DonateModel $donateModel,
    ) {
    }

    public function getRecentDonations(int $page = 1, int $limit = 20): Pagerfanta
    {
        $repository = $this->entityManager->getRepository(Donation::class);
        $query = $repository->getDonationListQuery();

        $adapter = new QueryAdapter($query);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

    public function getStatForDonations(): array
    {
        return $this->donateModel->getStatForDonations();
    }

    public function getDonationCampaignValues(): ?Params
    {
        $repository = $this->entityManager->getRepository(Params::class);
        $params = $repository->findAll();
        if (!empty($params)) {
            return $params[0];
        }

        return null;
    }

    public function startDonationCampaign(Params $params): void
    {
        $params->setToggledonatebar(1);
        $this->entityManager->persist($params);
        $this->entityManager->flush();
    }

    public function stopDonationCampaign(): void
    {
        $repository = $this->entityManager->getRepository(Params::class);
        $params = $repository->findAll();
        if (!empty($params)) {
            $param = $params[0];
            $param->setToggledonatebar(0);
            $this->entityManager->persist($param);
            $this->entityManager->flush();
        }
    }
}

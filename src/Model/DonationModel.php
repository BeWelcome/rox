<?php

namespace App\Model;

use App\Entity\Country;
use App\Entity\Donation;
use App\Entity\Member;
use App\Entity\NewLocation;
use App\Repository\NewLocationRepository;
use Doctrine\ORM\EntityManagerInterface;

class DonationModel
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function processDonation(?Member $member, array $parameters): bool
    {
        $donation = new Donation();
        $donation->setAmount($parameters['amt'] ?? 0);
        $donation->setMoney($parameters['cc'] ?? 'EUR');
        $donation->setMemberComment($parameters['cm'] ?? '');
        $donation->setDonor($member);

        if (null === $member) {
            $donation->setNameGiven('paypal');
        } else {
            $donation->setEmail($member->getEmail());
            $donation->setNameGiven($member->getUsername());

            /** @var NewLocationRepository $locationRepository */
            $locationRepository = $this->entityManager->getRepository(NewLocation::class);
            /** @var ?NewLocation $country */
            $country = $locationRepository->findCountry($member->getCountry()->getCountryId());

            if (null !== $country) {
                $donation->setCountry($country);
            }
        }
        $donation->setSystemComment('paypal');
        $donation->setReferencePaypal($parameters['tx'] ?? 'No transaction provided');
        $donation->setStatusPrivate('showamountonly');

        $this->entityManager->persist($donation);
        $this->entityManager->flush();

        return true;
    }

    public function getListOfDonations() : array
    {
        return [];
    }
}

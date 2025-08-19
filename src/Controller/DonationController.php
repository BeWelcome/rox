<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Entity\Member;
use App\Model\DonationModel;
use App\Repository\DonationRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hidehalo\Nanoid\Client;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DonationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private const string PAYPAL_NONCE = 'paypal_nonce';

    #[Route(path: '/donations', name: 'donations')]
    public function overview(Request $request): Response
    {
        $nanoIdClient = new Client();
        $nanoId = $nanoIdClient->generateId();

        $session = $request->getSession();
        if ($session->has(self::PAYPAL_NONCE)) {
            $session->remove(self::PAYPAL_NONCE);
        }
        $session->set(self::PAYPAL_NONCE, $nanoId);

        return $this->render('donation/overview.html.twig', ['nonce' => $nanoId]);
    }

    #[Route(path: '/donation/finish', name: 'finish_donation', methods: ['POST'])]
    public function finishDonation(Request $request, DonationModel $donationModel): JsonResponse
    {
        $session = $request->getSession();
        $nonce = $session->get(self::PAYPAL_NONCE);
        $session->remove(self::PAYPAL_NONCE);

        $parameters = json_decode($request->getContent(), true);

        if (!isset($parameters['nonce']) || $parameters['nonce'] !== $nonce) {
            return new JsonResponse(['success' => false], 403);
        }

        /** @var Member $donor */
        $donor = $this->getUser();
        $success = $donationModel->processDonation($donor, $parameters);

        return new JsonResponse(['success' => $success]);
    }

    #[Route(path: '/donation/complete', name: 'donation_complete')]
    public function donationCompletedSuccessfully(): RedirectResponse
    {
        $this->addTranslatedFlash('notice', 'donation.thanks');

        return $this->redirectToRoute('donations');
    }

    #[Route(path: '/donation/error', name: 'donation_error')]
    public function donationEndedInError(): RedirectResponse
    {
        $this->addTranslatedFlash('error', 'donation.error');

        return $this->redirectToRoute('donations');
    }

    #[Route(path: '/donations/list/{page}', name: 'donations_list')]
    public function listDonations(EntityManagerInterface $entityManager, int $page = 1): Response
    {
        /** @var DonationRepository $donationRepository */
        $donationRepository = $entityManager->getRepository(Donation::class);
        $donationQuery = $donationRepository->getDonationListQuery();

        $donations = new Pagerfanta(new QueryAdapter($donationQuery));
        $donations->setMaxPerPage(50);
        $donations->setCurrentPage($page);

        $member = $this->getUser();
        $isTreasurer = false;
        if (null !== $member) {
            $roles = $member->getRoles();
            $isTreasurer = \in_array(Member::ROLE_ADMIN_TREASURER, $roles, true);
        }

        return $this->render('donation/list.html.twig', [
            'donations' => $donations,
            'isTreasurer' => $isTreasurer,
        ]);
    }

    #[Route(path: '/donate', name: 'donate_redirect')]
    public function redirectDonate(): RedirectResponse
    {
        return $this->redirectToRoute('donations');
    }

    #[Route(path: '/donate/list', name: 'donate_list_redirect')]
    public function redirectDonateList(): RedirectResponse
    {
        return $this->redirectToRoute('donations_list');
    }
}

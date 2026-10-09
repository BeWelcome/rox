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
use Symfony\Component\Routing\Annotation\Route;

class DonationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private const PAYPAL_NONCE = 'paypal_nonce';
    // Amount of the donation just recorded, for the Plausible revenue event (#540).
    private const DONATION_ANALYTICS = 'donation_analytics';

    /**
     * @Route("/donations", name="donations")
     */
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

    /**
     * @Route("/donation/finish", name="finish_donation", methods={"POST"})
     */
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
        if ($success) {
            $session->set(self::DONATION_ANALYTICS, [
                'amount' => (float) ($parameters['amt'] ?? 0),
                'currency' => strtoupper((string) ($parameters['cc'] ?? 'EUR')),
            ]);
        }

        return new JsonResponse(['success' => $success]);
    }

    /**
     * @Route("/donation/complete", name="donation_complete")
     */
    public function donationCompletedSuccessfully(Request $request): RedirectResponse
    {
        $this->addTranslatedFlash('notice', 'donation.thanks');

        // Plausible revenue goal "Donation" (#540). Only for a donation recorded by
        // finishDonation in this session, so reloading this URL counts nothing.
        $donation = $request->getSession()->remove(self::DONATION_ANALYTICS);
        if (\is_array($donation) && $donation['amount'] > 0) {
            $this->addFlash('plausible_event', [
                'name' => 'Donation',
                'revenue' => ['currency' => $donation['currency'], 'amount' => $donation['amount']],
            ]);
        }

        return $this->redirectToRoute('donations');
    }

    /**
     * @Route("/donation/error", name="donation_error")
     */
    public function donationEndedInError(Request $request): RedirectResponse
    {
        $this->addTranslatedFlash('error', 'donation.error');

        return $this->redirectToRoute('donations');
    }

    /**
     * @Route("/donations/list/{page}", name="donations_list")
     */
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

    /**
     * @Route("/donate", name="donate_redirect")
     */
    public function redirectDonate(): RedirectResponse
    {
        return $this->redirectToRoute('donations');
    }

    /**
     * @Route("/donate/list", name="donate_list_redirect")
     */
    public function redirectDonateList(): RedirectResponse
    {
        return $this->redirectToRoute('donations_list');
    }
}

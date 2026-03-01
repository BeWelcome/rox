<?php

namespace App\Controller\Admin;

use App\Entity\Donation;
use App\Entity\Member;
use App\Form\Admin\CampaignType;
use App\Form\Admin\DonationType;
use App\Model\Admin\TreasurerModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TreasurerController extends AbstractController
{
    public function __construct(
        private readonly TreasurerModel $treasurerModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/admin/treasurer', name: 'admin_treasurer_overview')]
    public function overview(Request $request): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_TREASURER)) {
            throw $this->createAccessDeniedException('You need to have Treasurer right to access this.');
        }

        $page = $request->query->getInt('page', 1);
        $donations = $this->treasurerModel->getRecentDonations($page);
        $stats = $this->treasurerModel->getStatForDonations();
        $params = $this->treasurerModel->getDonationCampaignValues();

        return $this->render('admin/treasurer/index.html.twig', [
            'donations' => $donations,
            'stats' => $stats,
            'params' => $params,
        ]);
    }

    #[Route('/admin/treasurer/donation/edit/{id}', name: 'admin_treasurer_donation_edit', defaults: ['id' => null])]
    #[Route('/admin/treasurer/add', name: 'admin_treasurer_donation_add')]
    public function editDonation(Request $request, ?Donation $donation = null): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_TREASURER)) {
            throw $this->createAccessDeniedException();
        }

        if (!$donation) {
            $donation = new Donation();
        }

        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($donation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Donation saved.');

            return $this->redirectToRoute('admin_treasurer_overview');
        }

        return $this->render('admin/treasurer/edit_donation.html.twig', [
            'form' => $form->createView(),
            'donation' => $donation,
        ]);
    }

    #[Route('/admin/treasurer/campaign/start', name: 'admin_treasurer_campaign_start')]
    public function startCampaign(Request $request): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_TREASURER)) {
            throw $this->createAccessDeniedException();
        }

        $params = $this->treasurerModel->getDonationCampaignValues();
        if (!$params) {
            throw $this->createNotFoundException('Params not found');
        }

        $form = $this->createForm(CampaignType::class, $params);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->treasurerModel->startDonationCampaign($params);
            $this->addFlash('success', 'Campaign started.');

            return $this->redirectToRoute('admin_treasurer_overview');
        }

        return $this->render('admin/treasurer/start_campaign.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/treasurer/campaign/stop', name: 'admin_treasurer_campaign_stop')]
    public function stopCampaign(): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_TREASURER)) {
            throw $this->createAccessDeniedException();
        }

        $this->treasurerModel->stopDonationCampaign();
        $this->addFlash('success', 'Campaign stopped.');

        return $this->redirectToRoute('admin_treasurer_overview');
    }
}

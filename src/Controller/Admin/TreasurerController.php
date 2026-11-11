<?php

namespace App\Controller\Admin;

use App\Entity\Donation;
use App\Entity\Member;
use App\Form\Admin\CampaignType;
use App\Form\Admin\DonationType;
use App\Model\Admin\TreasurerModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Member::ROLE_ADMIN_TREASURER)]
class TreasurerController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly TreasurerModel $treasurerModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/admin/treasurer', name: 'admin_treasurer_overview')]
    public function overview(Request $request): Response
    {
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
        if (!$donation) {
            $donation = new Donation();
            $donation->setCreated(new DateTime('today'));
        }

        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($donation);
            $this->entityManager->flush();

            $this->addTranslatedFlash('notice', 'admin.treasurer.donation.saved');

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
        $params = $this->treasurerModel->getDonationCampaignValues();
        if (!$params) {
            throw $this->createNotFoundException('Params not found');
        }

        $form = $this->createForm(CampaignType::class, $params);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->treasurerModel->startDonationCampaign($params);
            $this->addTranslatedFlash('notice', 'admin.treasurer.campaign.started');

            return $this->redirectToRoute('admin_treasurer_overview');
        }

        return $this->render('admin/treasurer/start_campaign.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/treasurer/campaign/stop', name: 'admin_treasurer_campaign_stop', methods: ['POST'])]
    #[IsCsrfTokenValid('treasurer_campaign_stop')]
    public function stopCampaign(): Response
    {
        $this->treasurerModel->stopDonationCampaign();
        $this->addTranslatedFlash('notice', 'admin.treasurer.campaign.stopped');

        return $this->redirectToRoute('admin_treasurer_overview');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Model\GalleryModel;
use App\Model\TranslationModel;
use App\Repository\MemberRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hidehalo\Nanoid\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MassmailController extends AbstractController
{
    /**
     * @Route("/admin/massmail/test/{id}", name="admin_massmail_test",
     *     requirements={"id"="\d+"})
     */
    public function testMassmailSending(
        Request $request,
        Newsletter $newsletter,
        EntityManagerInterface $entityManager,
        Mailer $mailer
    ): Response {
        if (!$this->isGranted(Member::ROLE_ADMIN_MASSMAIL)) {
            throw $this->createAccessDeniedException('You need to have Massmail right to access this.');
        }

        $formBuilder = $this->createFormBuilder();
        $form = $formBuilder
            ->add('members', TextType::class, [
                'help' => 'Use a list of usernames separated by commas. E. g. polyglot, gbenouville, shevek',
                'required' => true
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $members = array_map('trim', explode(',', $data['members']));

            /** @var MemberRepository $memberRepository */
            $memberRepository = $entityManager->getRepository(Member::class);
            $memberEntities = $memberRepository->findBy(['username' => $members]);

            foreach ($memberEntities as $member) {
                $mailer->sendNewsletterEmail($newsletter, $member, [
                    'unsubscribe_key' => 'ebe4add6264a01e54cb4f1bb774f36886bbf11d236f03444072760cf4ee3e2dd'
                ]);
            }

            return $this->redirectToRoute('admin_massmail');
        }

        return $this->render('admin/massmail/test.sending.html.twig', [
            'form' => $form->createView(),
            'newsletter' => $newsletter,
        ]);
    }
}

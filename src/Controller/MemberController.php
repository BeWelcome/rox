<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\PasswordFormType;
use App\Logger\Logger;
use App\Model\MemberModel;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

/**
 * Class MemberController.
 */
class MemberController extends AbstractController
{
    use ManagerTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /**
     * @Route("/mydata", name="member_personal_data")
     *
     * @throws Exception
     *
     * @return StreamedResponse|Response
     */
    public function getPersonalDataSelf(
        Request $request,
        MemberModel $memberModel,
        Security $security,
        EntrypointLookupInterface $entrypointLookup,
        PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
        $passwordForm = $this->createForm(PasswordFormType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            /** @var Member $member */
            $member = $this->getUser();
            $password = $passwordForm->get('password')->getData();

            $token = $security->getToken();

            if ($token) {
                $passwordHasher = $passwordHasherFactory->getPasswordHasher($member);

                if ($passwordHasher->verify($member->getPassword(), $password)) {
                    // Collect information and store in zip file
                    $zipFilename = $memberModel->collectPersonalData($member);

                    $request->getSession()->set('mydata_file', $zipFilename);
                    $entrypointLookup->reset();

                    return $this->render('private/download.html.twig', [
                        'username' => $member->getUsername(),
                        'url' => $this->generateUrl(
                            'member_download_data',
                            ['username' => $member->getUsername()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    ]);
                }
                $passwordForm->addError(new FormError($this->translator->trans('form.error.password.incorrect')));
            }
        }

        return $this->render('private/password.html.twig', [
            'form' => $passwordForm->createView(),
        ]);
    }

    /**
     * @Route("/members/{username}/data", name="admin_personal_data")
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    public function getPersonalData(
        Request $request,
        Member $member,
        Logger $logger,
        EntrypointLookupInterface $entrypointLookup,
        MemberModel $memberModel
    ): Response {
        // Either the member themselves or a person from the safety or the admin can access
        $this->denyAccessUnlessGranted(
            Member::ROLE_ADMIN_ADMIN,
            null,
            'Unable to access this page!'
        );

        $logger->write('Extracting personal data for ' . $member->getUsername(), 'Members');

        $zipFilename = $memberModel->collectPersonalData($member);

        $request->getSession()->set('mydata_file', $zipFilename);

        $entrypointLookup->reset();

        return $this->render('private/download.html.twig', [
            'username' => $member->getUsername(),
            'url' => $this->generateUrl(
                'member_download_data',
                [
                    'username' => $member->getUsername(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }

    /**
     * @Route("/mydata/{username}/download", name="member_download_data")
     *
     * @throws Exception
     *
     * @return BinaryFileResponse|RedirectResponse
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    public function downloadPersonalData(Request $request, Member $member)
    {
        $zipFilename = $request->getSession()->get('mydata_file');
        if (file_exists($zipFilename)) {
            // main dir is left over!
            $response = new BinaryFileResponse($zipFilename);
            $response->headers->set('Content-Type', 'application/zip');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
            $response->deleteFileAfterSend(true);

            return $response;
        }

        return new RedirectResponse($this->generateUrl('members_profile', ['username' => $member->getUsername()]));
    }
}

<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\PasswordFormType;
use App\Logger\Logger;
use App\Model\MemberModel;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ManagerTrait;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

/**
 * Class MemberController.
 */
class MemberController extends AbstractController
{
    use ManagerTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(private ProfileSubmenu $profileSubmenu, private ChangeProfilePictureGlobals $globals)
    {
    }

    #[Route(path: '/mydata', name: 'profile_personal_data_redirect')]
    public function redirectMyData()
    {
        $username = $this->getUser()->getUsername();

        return $this->redirectToRoute('profile_personal_data', [
            'username' => $username,
        ]);
    }

    /**
     * @throws Exception
     *
     * @return StreamedResponse|Response
     */
    #[Route(path: '/members/{username:member}/mydata', name: 'profile_personal_data')]
    public function getPersonalDataSelf(
        Request $request,
        Member $member,
        MemberModel $memberModel,
        Security $security,
        EntrypointLookupInterface $entrypointLookup,
        PasswordHasherFactoryInterface $passwordHasherFactory,
    ): Response|RedirectResponse {
        /** @var Member $member */
        $loggedInMember = $this->getUser();

        if ($member->getUsername() !== $loggedInMember->getUsername()) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        $passwordForm = $this->createForm(PasswordFormType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
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
                        'member' => $member,
                        'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
                        'submenu' => $this->profileSubmenu->getSubmenu($member, $member),
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
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $member),
            'form' => $passwordForm->createView(),
        ]);
    }

    /**
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    #[Route(path: '/members/{username:member}/data', name: 'admin_personal_data')]
    public function getPersonalData(
        Request $request,
        Member $member,
        Logger $logger,
        EntrypointLookupInterface $entrypointLookup,
        MemberModel $memberModel,
    ): Response {
        // Only the admin can access this special page
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
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($member, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($member, $member),
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
     * @throws Exception
     *
     * @return BinaryFileResponse|RedirectResponse
     *
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    #[Route(path: '/mydata/{username:member}/download', name: 'member_download_data')]
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

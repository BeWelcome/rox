<?php

namespace App\Controller\Admin;

use App\Doctrine\DomainType;
use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Word;
use App\Form\Admin\MassmailRecipientsType;
use App\Form\Admin\MassmailType;
use App\Model\Admin\MassmailModel;
use App\Model\Admin\MassmailRights;
use App\Model\TranslationModel;
use App\Repository\MemberRepository;
use App\Service\Mailer;
use App\Utilities\ItemsPerPageTraits;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Mass mails: volunteers with the MassMail right write them (in English, translated like other words), choose the
 * recipients and, from level 5, trigger the sending; send:massmail sends them.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 */
#[IsGranted(Member::ROLE_ADMIN_MASSMAIL)]
class MassmailController extends AbstractController
{
    use ItemsPerPageTraits;

    public const string TOKEN = 'admin_massmail';

    public function __construct(
        private readonly MassmailModel $model,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly TranslationModel $translationModel,
    ) {
    }

    #[Route(path: '/admin/massmail/{page}', name: 'admin_massmail', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function list(int $page = 1): Response
    {
        return $this->render('admin/massmail/list.html.twig', [
            'massmails' => $this->model->getPager($page, $this->getItemsPerPage($this->volunteer())),
            'rights' => $this->rights(),
        ]);
    }

    #[Route(path: '/admin/massmail/create', name: 'admin_massmail_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $rights = $this->rights();
        $form = $this->createForm(MassmailType::class, null, ['types' => $rights->types]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $name = strtolower((string) $form->get('name')->getData());
            if ($this->model->wordCodeExists($name)) {
                $form->get('name')->addError(new FormError($this->translator->trans('adminmassmailcodeexists')));
            } else {
                $newsletter = new Newsletter();
                $newsletter
                    ->setName($name)
                    ->setType($form->get('type')->getData())
                    ->setStatus('Created')
                    ->setCreated(new DateTime())
                    ->setCreatedBy($this->volunteer());
                $this->entityManager->persist($newsletter);
                $description = (string) $form->get('description')->getData();
                $this->entityManager->persist($this->word('broadcast_title_' . $name, (string) $form->get('subject')->getData(), $description));
                $this->entityManager->persist($this->word('broadcast_body_' . $name, (string) $form->get('body')->getData(), $description));
                $this->entityManager->flush();
                $this->translationModel->refreshTranslationsCache();
                $this->addFlash('notice', $this->translator->trans('adminmassmailsuccesscreate'));

                return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
            }
        }

        return $this->render('admin/massmail/form.html.twig', [
            'form' => $form->createView(),
            'massmail' => null,
        ]);
    }

    #[Route(path: '/admin/massmail/{id}/edit', name: 'admin_massmail_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Newsletter $newsletter): Response
    {
        $massmail = $this->model->get($newsletter);
        if (!$massmail['can_edit']) {
            return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
        }

        $rights = $this->rights();
        $name = strtolower((string) $newsletter->getName());
        $subject = $this->englishWord('broadcast_title_' . $name);
        $body = $this->englishWord('broadcast_body_' . $name);
        $form = $this->createForm(MassmailType::class, [
            'type' => $newsletter->getType(),
            'name' => $name,
            'subject' => $subject?->getSentence(),
            'body' => $body?->getSentence(),
            'description' => $body?->getDescription(),
        ], [
            'types' => array_values(array_unique([...$rights->types, (string) $newsletter->getType()])),
            'edit' => true,
            'change_type' => $rights->canChangeType,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($rights->canChangeType) {
                $newsletter->setType($form->get('type')->getData());
            }
            $newsletter->setCreated(new DateTime())->setCreatedBy($this->volunteer());
            foreach ([[$subject, 'subject'], [$body, 'body']] as [$word, $field]) {
                $word?->setSentence((string) $form->get($field)->getData())
                    ->setUpdated(new DateTime())
                    ->setAuthor($this->volunteer());
            }
            $this->entityManager->flush();
            $this->translationModel->refreshTranslationsCache();
            $this->addFlash('notice', $this->translator->trans('adminmassmailsuccessedit'));

            return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
        }

        return $this->render('admin/massmail/form.html.twig', [
            'form' => $form->createView(),
            'massmail' => $massmail,
        ]);
    }

    #[Route(path: '/admin/massmail/{id}/details', name: 'admin_massmail_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(Newsletter $newsletter): Response
    {
        return $this->render('admin/massmail/details.html.twig', [
            'newsletter' => $newsletter,
            'massmail' => $this->model->get($newsletter),
            'languages' => $this->model->getLanguages($newsletter),
            'rights' => $this->rights(),
        ]);
    }

    #[Route(path: '/admin/massmail/{id}/enqueue', name: 'admin_massmail_enqueue', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function enqueue(Request $request, Newsletter $newsletter): Response
    {
        $massmail = $this->model->get($newsletter);
        if (!$massmail['can_enqueue']) {
            return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
        }

        $rights = $this->rights();
        $choices = $rights->recipients;
        if (Newsletter::TERMS_OF_USE !== $newsletter->getType()) {
            $choices = array_values(array_diff($choices, [MassmailRights::TERMS_OF_USE]));
        }

        $forms = [];
        foreach ($choices as $recipients) {
            $forms[$recipients] = $this->container->get('form.factory')->createNamed($recipients, MassmailRecipientsType::class, null, [
                'recipients' => $recipients,
                'countries' => MassmailRights::LOCATION === $recipients ? $this->model->getCountries($rights->countries) : [],
                'groups' => MassmailRights::GROUP === $recipients ? $this->model->getGroups($rights->groups) : [],
            ]);
        }

        $active = $choices[0] ?? null;
        foreach ($forms as $recipients => $form) {
            $form->handleRequest($request);
            if (!$form->isSubmitted()) {
                continue;
            }
            $active = $recipients;
            if ($form->isValid()) {
                $count = $this->enqueueRecipients($newsletter, $recipients, $form);
                if (null !== $count) {
                    $this->addFlash('notice', \sprintf(
                        $this->translator->trans('adminmassmailsuccessenqueue'),
                        $newsletter->getName(),
                        $count,
                    ));

                    return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
                }
            }
        }

        return $this->render('admin/massmail/enqueue.html.twig', [
            'newsletter' => $newsletter,
            'forms' => array_map(static fn (FormInterface $form) => $form->createView(), $forms),
            'active' => $active,
        ]);
    }

    #[Route(path: '/admin/massmail/{id}/unqueue', name: 'admin_massmail_unqueue', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function unqueue(Newsletter $newsletter): RedirectResponse
    {
        if ($this->model->get($newsletter)['can_unqueue']) {
            $this->model->unqueue($newsletter);
            $this->addFlash('notice', $this->translator->trans('adminmassmailsuccessunqueue'));
        }

        return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
    }

    #[Route(path: '/admin/massmail/{id}/trigger', name: 'admin_massmail_trigger', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function trigger(Newsletter $newsletter): RedirectResponse
    {
        $this->denyUnlessCanTrigger();
        if ($this->model->get($newsletter)['can_trigger']) {
            $this->model->trigger($newsletter);
            $this->addFlash('notice', $this->translator->trans('adminmassmailsuccesstrigger'));
        }

        return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
    }

    #[Route(path: '/admin/massmail/{id}/untrigger', name: 'admin_massmail_untrigger', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function untrigger(Newsletter $newsletter): RedirectResponse
    {
        $this->denyUnlessCanTrigger();
        if ($this->model->get($newsletter)['can_untrigger']) {
            $count = $this->model->untrigger($newsletter);
            $this->addFlash('notice', $this->translator->trans('admin.massmail.untriggered', ['%count%' => $count]));
        }

        return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
    }

    #[Route(path: '/admin/massmail/adminunits/{country}', name: 'admin_massmail_adminunits', requirements: ['country' => '[A-Z]{2}'], methods: ['GET'])]
    public function adminUnits(string $country): JsonResponse
    {
        return $this->json($this->model->getAdminUnits($country));
    }

    #[Route(
        path: '/admin/massmail/places/{country}/{adminUnit}',
        name: 'admin_massmail_places',
        requirements: ['country' => '[A-Z]{2}', 'adminUnit' => '[A-Za-z0-9]{1,20}'],
        methods: ['GET'],
    )]
    public function places(string $country, string $adminUnit): JsonResponse
    {
        return $this->json($this->model->getPlaces($country, $adminUnit));
    }

    /**
     * Sends the mass mail to some members, without enqueueing it.
     */
    #[Route(path: '/admin/massmail/{id}/test', name: 'admin_massmail_test', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function test(Request $request, Newsletter $newsletter, MemberRepository $memberRepository, Mailer $mailer): Response
    {
        $form = $this->createFormBuilder()
            ->add('members', TextType::class, [
                'help' => 'Use a list of usernames separated by commas. E. g. polyglot, gbenouville, shevek',
                'required' => true,
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $usernames = $this->usernames((string) $form->get('members')->getData());
            foreach ($memberRepository->findBy(['username' => $usernames]) as $member) {
                $mailer->sendNewsletterEmail($newsletter, $member, [
                    'unsubscribe_key' => 'ebe4add6264a01e54cb4f1bb774f36886bbf11d236f03444072760cf4ee3e2dd',
                ]);
            }

            return $this->redirectToRoute('admin_massmail_details', ['id' => $newsletter->getId()]);
        }

        return $this->render('admin/massmail/test.sending.html.twig', [
            'form' => $form->createView(),
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * The URLs of the legacy pages.
     */
    #[Route(path: '/admin/massmail/details/{id}', name: 'admin_massmail_details_old', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Route(
        path: '/admin/massmail/details/{id}/{status}/{rest}',
        name: 'admin_massmail_recipients_old',
        requirements: ['id' => '\d+', 'status' => 'enqueued|triggered|sent|failed', 'rest' => 'page/\d+'],
        defaults: ['rest' => ''],
        methods: ['GET'],
    )]
    #[Route(path: '/admin/massmail/edit/{id}', name: 'admin_massmail_edit_old', requirements: ['id' => '\d+'], methods: ['GET'], defaults: ['target' => 'admin_massmail_edit'])]
    #[Route(path: '/admin/massmail/enqueue/{id}', name: 'admin_massmail_enqueue_old', requirements: ['id' => '\d+'], methods: ['GET'], defaults: ['target' => 'admin_massmail_enqueue'])]
    #[Route(path: '/admin/massmail/test/{id}', name: 'admin_massmail_test_old', requirements: ['id' => '\d+'], methods: ['GET'], defaults: ['target' => 'admin_massmail_test'])]
    public function oldPage(int $id, string $target = 'admin_massmail_details'): RedirectResponse
    {
        return $this->redirectToRoute($target, ['id' => $id], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @return int|null the number of enqueued members; null if the form has an error now
     */
    private function enqueueRecipients(Newsletter $newsletter, string $recipients, FormInterface $form): ?int
    {
        $volunteer = $this->volunteer();
        switch ($recipients) {
            case MassmailRights::MEMBERS:
                $usernames = [];
                if (MassmailRecipientsType::USERNAMES === $form->get('members')->getData()) {
                    $usernames = $this->usernames((string) $form->get('usernames')->getData());
                    if ([] === $usernames) {
                        $form->get('usernames')->addError(new FormError($this->translator->trans('admin.massmail.usernames.empty')));

                        return null;
                    }
                }

                return $this->model->enqueueMembers($newsletter, $volunteer, $usernames, (int) $form->get('limit')->getData());
            case MassmailRights::LOCATION:
                $adminUnit = (string) $form->get('adminUnit')->getData();
                $place = $form->get('place')->getData();

                return $this->model->enqueueLocation(
                    $newsletter,
                    $volunteer,
                    (string) $form->get('country')->getData(),
                    '' === $adminUnit ? null : $adminUnit,
                    null === $place || 0 === (int) $place ? null : (int) $place,
                );
            case MassmailRights::GROUP:
                return $this->model->enqueueGroup($newsletter, $volunteer, (int) $form->get('group')->getData());
            default:
                return $this->model->enqueueTermsOfUse($newsletter, $volunteer);
        }
    }

    /**
     * @return list<string>
     */
    private function usernames(string $list): array
    {
        return array_values(array_unique(array_filter(array_map(trim(...), preg_split('/[;,\s]+/', $list) ?: []))));
    }

    private function word(string $code, string $sentence, string $description): Word
    {
        /** @var Language $english */
        $english = $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => 'en']);

        $word = new Word();
        $word
            ->setCode($code)
            ->setLanguage($english)
            ->setDomain(DomainType::ICU_MESSAGES)
            ->setSentence($sentence)
            ->setDescription($description)
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setTranslationPriority(5)
            ->setIsArchived(false)
            ->setAuthor($this->volunteer())
            ->setCreated(new DateTime())
            ->setUpdated(new DateTime())
            ->setMajorUpdate(new DateTime());

        return $word;
    }

    private function englishWord(string $code): ?Word
    {
        return $this->entityManager->getRepository(Word::class)->findOneBy(['code' => $code, 'language' => 'en']);
    }

    private function denyUnlessCanTrigger(): void
    {
        if (!$this->rights()->canTrigger) {
            throw $this->createAccessDeniedException();
        }
    }

    private function rights(): MassmailRights
    {
        return MassmailRights::of($this->volunteer());
    }

    private function volunteer(): Member
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $member;
    }
}

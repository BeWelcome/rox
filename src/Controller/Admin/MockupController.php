<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Model\MockupProvider\MockupProviderInterface;
use App\Model\Translation\MockupPreview;
use App\Model\Translation\TranslationTool;
use App\Model\TranslationModel;
use App\Twig\MockupExtension;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings("PHPMD")
 */
class MockupController extends TranslationController
{
    private $mockups = [];
    /** @var iterable|MockupProviderInterface[] */
    private $providers;

    public function __construct(
        TranslationModel $translationModel,
        EntityManagerInterface $entityManager,
        array $locales,
        iterable $providers,
    ) {
        parent::__construct($translationModel, $entityManager, $locales);

        foreach ($providers as $provider) {
            $feature = $provider->getFeature();
            $this->providers[$feature] = $provider;
            $this->mockups = array_merge([$feature => $provider->getMockups()], $this->mockups);
        }
    }

    /**
     * The features with their mails and pages, the first one open.
     */
    #[Route(path: '/admin/translations/mockups', name: 'translations_mockups')]
    public function selectMockup(Request $request, TranslationTool $translationTool): Response
    {
        return $this->showMockups($request, $translationTool, null);
    }

    /**
     * The features, this one open: its mails and pages, each to preview in the site's language.
     */
    #[Route(path: '/admin/translations/mockups/{feature}', name: 'translations_mockups_feature')]
    public function selectMockupsFeature(Request $request, string $feature, TranslationTool $translationTool): Response
    {
        if (!\array_key_exists($feature, $this->providers)) {
            return $this->redirectToRoute('translations_mockups');
        }

        return $this->showMockups($request, $translationTool, $feature);
    }

    /**
     * A mail or a page with test data, as members get it, its texts listed for translation below it. Variants are
     * picked by their parameter in the query (?count=2).
     */
    #[Route(path: '/admin/translate/mockup/{feature}/{name}', name: 'translation_mockup', methods: ['GET'])]
    public function translateMockup(
        Request $request,
        string $feature,
        string $name,
        TranslationTool $translationTool,
        MockupPreview $mockupPreview,
        LoggerInterface $logger,
    ): Response {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $mockup = $this->mockups[$feature][$name] ?? null;
        if (null === $mockup) {
            return $this->redirectToRoute('translations_mockups_feature', ['feature' => $feature]);
        }

        /** @var Member $translator */
        $translator = $this->getUser();
        $variants = $this->getVariants($feature, $name, $request->getLocale());
        $variant = $this->getVariant($request, $variants);

        // a broken mockup (its test data or its template) shows as such: the translator can report it
        $preview = null;
        try {
            $parameters = array_merge(['name' => $name], $variant['parameters'] ?? [], $this->getGeneralMockupParameters());
            $variables = $this->providers[$feature]->getMockupVariables($parameters);
            if ('email' === $mockup['type']) {
                // the mailer's wrapped email: images are links to the site instead of attachments
                $variables['email'] = new MockupExtension();
            }
            $preview = $mockupPreview->render($mockup['template'], $variables);
        } catch (Throwable $exception) {
            $logger->error('Mockup {feature}/{name} failed: {message}', [
                'feature' => $feature,
                'name' => $name,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }

        return $this->render('admin/translations/tool/mockup.html.twig', [
            'locale' => $this->getToolLocale($request, $translator),
            'languageMenu' => $this->getToolLanguages($translationTool, $translator),
            'feature' => $feature,
            'name' => $name,
            'mockup' => $mockup,
            'variants' => $variants,
            'variant' => $variant,
            'preview' => $preview,
        ]);
    }

    private function showMockups(Request $request, TranslationTool $translationTool, ?string $feature): Response
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($request->getLocale())) {
            return $this->redirectToRoute('translations_no_permissions', ['locale' => $request->getLocale()]);
        }

        $features = array_keys($this->providers);
        sort($features);
        $feature ??= $features[0];

        return $this->render('admin/translations/tool/mockups.html.twig', [
            'locale' => $this->getToolLocale($request, $translator),
            'languageMenu' => $this->getToolLanguages($translationTool, $translator),
            'features' => array_map(fn (string $name): array => [
                'name' => $name,
                'count' => \count($this->providers[$name]->getMockups()),
            ], $features),
            'feature' => $feature,
            'provider' => $this->providers[$feature],
        ]);
    }

    private function getGeneralMockupParameters(): array
    {
        // Use the bwAdmin account as counterpart
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $bwAdmin = $memberRepository->find(1);

        return [
            'admin' => $bwAdmin,
            'user' => $this->getUser(),
        ];
    }

    /**
     * The variants of a mockup with parameters: a label and the parameters of each.
     *
     * @return list<array{label: string, parameters: array<string, string>}>
     */
    private function getVariants(string $feature, string $name, string $locale): array
    {
        if (true !== ($this->mockups[$feature][$name]['with_parameters'] ?? false)) {
            return [];
        }

        $variants = [];
        foreach ($this->providers[$feature]->getMockupParameter($locale, $name) as $parameter => $values) {
            foreach ($values as $label => $value) {
                $variants[] = [
                    'label' => ucfirst((string) $parameter) . ': ' . $label,
                    'parameters' => [$parameter => (string) $value],
                ];
            }
        }

        return $variants;
    }

    /**
     * The variant the query asks for, the first one otherwise; null for a mockup without variants.
     *
     * @param list<array{label: string, parameters: array<string, string>}> $variants
     *
     * @return array{label: string, parameters: array<string, string>}|null
     */
    private function getVariant(Request $request, array $variants): ?array
    {
        foreach ($variants as $variant) {
            $asked = array_intersect_key($request->query->all(), $variant['parameters']);
            if ($asked === $variant['parameters']) {
                return $variant;
            }
        }

        return $variants[0] ?? null;
    }
}

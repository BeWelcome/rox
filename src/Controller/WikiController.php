<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Wiki;
use App\Model\WikiModel;
use App\Repository\WikiRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WikiController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /**
     * @Route("/wiki", name="wiki_front_page")
     *
     * @return Response
     */
    public function showWikiFrontPage(WikiModel $wikiModel)
    {
        return $this->showWikiPage('WikiFrontPage', $wikiModel, 0);
    }

    /**
     * @Route("/wiki/recent", name="wiki_recent")
     *
     * @return Response
     */
    public function showRecentChanges(Request $request)
    {
        $page = $request->get('page', 1);

        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $this->getDoctrine()->getRepository(Wiki::class);
        $recentChanges = $wikiRepository->getRecentChanges();
        $adapter = new ArrayAdapter($recentChanges);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(20);
        $pagerFanta->setCurrentPage($page);

        return $this->render('wiki/recent.html.twig', [
            'submenu' => [
                'active' => 'recent',
                'items' => $this->getSubmenuItems(),
            ],
            'pager' => $pagerFanta,
        ]);
    }

    /**
     * @Route("/wiki/{pageTitle}/{version}", name="wiki_page",
     *     requirements={"version"="\d+"},
     *     defaults={"version"=0})
     *
     * @param $pageTitle
     *
     * @return Response
     */
    public function showWikiPage($pageTitle, WikiModel $wikiModel, int $version)
    {
        $pageName = $wikiModel->getPageName($pageTitle);

        $em = $this->getDoctrine();
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $em->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName, $version);

        $pagerFanta = null;
        $content = null;
        if (null === $wikiPage) {
            // No wiki page found if no version was given create a new page.
            if (0 === $version) {
                return $this->redirectToRoute('wiki_page_create', ['pageTitle' => $pageTitle]);
            }

            // the given version of the wiki page doesn't exist. Just keep going
            // (show appropriate message in the template)
        } else {
            $content = $wikiModel->parseWikiMarkup($wikiPage->getContent());
            if (null === $content) {
                $this->addTranslatedFlash('error', 'flash.wiki.markup.invalid');

                return $this->redirectToRoute('wiki_page_edit', ['pageTitle' => $pageTitle]);
            }

            // Create paginator
            $history = $wikiModel->getHistory($wikiPage);

            $adapter = new ArrayAdapter($history);
            $pagerFanta = new Pagerfanta($adapter);
            $pagerFanta->setMaxPerPage(1);
            if (0 === $version) {
                $pagerFanta->setCurrentPage($pagerFanta->getNbResults());
            } else {
                $pagerFanta->setCurrentPage($version);
            }
        }

        $frontPage = 'WikiFrontPage' === $pageTitle;
        $activePage = $frontPage ? 'startpage' : 'currentpage';
        $currentPage = $frontPage ? null : $pageTitle;

        return $this->render('wiki/wiki.html.twig', [
            'title' => $pageTitle,
            'wikipage' => $wikiPage,
            'content' => $content,
            'history' => $pagerFanta,
            'submenu' => [
                'active' => $activePage,
                'items' => $this->getSubmenuItems($currentPage),
            ],
        ]);
    }

    /**
     * @Route("/wiki/{pageTitle}/edit", name="wiki_page_edit")
     *
     * @param $pageTitle
     *
     * @return Response
     */
    public function editWikiPage(Request $request, WikiModel $wikiModel, $pageTitle)
    {
        /** @var Wiki $wikiPage */
        $wikiPage = $wikiModel->getPage($pageTitle);

        if (null === $wikiPage) {
            return $this->redirectToRoute('wiki_page_create', ['pageTitle' => $pageTitle]);
        }

        $form = $this->createFormBuilder(['wiki_markup' => $wikiPage->getContent()])
            ->add('wiki_markup', TextAreaType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Update Page',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var Member $member */
            $member = $this->getUser();
            $newWikiPage = clone $wikiPage;
            $newWikiPage->setContent($data['wiki_markup']);
            // \todo make this safe against multiple edits at the same time
            $newWikiPage->setVersion($wikiPage->getVersion() + 1);
            $newWikiPage->setAuthor($member->getUsername());
            $newWikiPage->setCreated((new DateTime())->getTimestamp());
            $em = $this->getDoctrine()->getManager();
            $em->persist($newWikiPage);
            $em->flush();
            $this->addTranslatedFlash('notice', 'flash.wiki.updated');

            return $this->redirectToRoute('wiki_page', [
                'pageTitle' => $pageTitle,
                'submenu' => [
                    'active' => 'edit_create',
                    'items' => $this->getSubmenuItems($pageTitle),
                ],
            ]);
        }

        return $this->render('wiki/edit_create.html.twig', [
            'title' => $pageTitle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/wiki/{pageTitle}/create", name="wiki_page_create")
     *
     * @param $pageTitle
     *
     * @return Response
     */
    public function createWikiPage(Request $request, WikiModel $wikiModel, $pageTitle)
    {
        $wikiPage = $wikiModel->getPage($pageTitle);

        if (null !== $wikiPage) {
            return $this->redirectToRoute('wiki_page_edit', ['pageTitle' => $pageTitle]);
        }

        $form = $this->createFormBuilder(['wiki_markup' => ''])
            ->add('wiki_markup', TextAreaType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Create Page',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $wikiModel->createWikiPage($pageTitle, $data['wiki_markup']);
            $this->addTranslatedFlash('notice', 'flash.wiki.created');

            return $this->redirectToRoute('wiki_page', ['pageTitle' => $pageTitle]);
        }

        return $this->render('wiki/edit_create.html.twig', [
            'title' => $pageTitle,
            'form' => $form->createView(),
        ]);
    }

    private function getSubmenuItems(?string $currentPage = null): array
    {
        $submenuItems = [];
        $submenuItems['startpage'] = [
            'key' => 'startpage',
            'url' => $this->generateUrl('wiki_front_page'),
        ];
        if (null !== $currentPage) {
            $submenuItems['currentpage'] = [
                'key' => $currentPage,
                'url' => $this->generateUrl('wiki_page', ['pageTitle' => $currentPage]),
            ];
        }
        $submenuItems['recent'] = [
            'key' => 'wiki.recent',
            'url' => $this->generateUrl('wiki_recent'),
        ];

        return $submenuItems;
    }
}

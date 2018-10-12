<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Member;
use AppBundle\Entity\Wiki;
use AppBundle\Repository\WikiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WikiController extends Controller
{
    /**
     * @Route("/wiki", name="wiki_front_page")
     *
     * @return Response
     */
    public function showWikiFrontPageAction()
    {
        return $this->showWikiPageAction('WikiFrontPage');
    }

    /**
     * @Route("/wiki/{pageTitle}", name="wiki_page")
     *
     * @param $pageTitle
     *
     * @return Response
     */
    public function showWikiPageAction($pageTitle)
    {
        $wikiModel = $this->get('rox.wiki.model');
        $pageName = $wikiModel->getPageName($pageTitle);

        $em = $this->getDoctrine();
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $em->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName);

        if (null === $wikiPage) {
            return $this->redirectToRoute('wiki_page_create', ['pageTitle' => $pageTitle]);
        }
        $output = $wikiModel->parseWikiMarkup($wikiPage->getContent());
        if (null === $output) {
            $this->addFlash('error', 'Couldn\'t process markup. Please check content and fix.');

            return $this->redirectToRoute('wiki_page_edit', ['pageTitle' => $pageTitle]);
        }

        return $this->render(':wiki:wiki.html.twig', [
            'title' => $pageTitle,
            'wikipage' => $output,
        ]);
    }

    /**
     * @Route("/wiki/{pageTitle}/edit", name="wiki_page_edit")
     *
     * @param Request $request
     * @param $pageTitle
     *
     * @return Response
     */
    public function editWikiPageAction(Request $request, $pageTitle)
    {
        $wikiModel = $this->get('rox.wiki.model');
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
        if ($form->isValid() && $form->isSubmitted()) {
            $data = $form->getData();
            $newWikiPage = clone $wikiPage;
            $newWikiPage->setContent($data['wiki_markup']);
            // \todo make this safe against multiple edits at the same time
            $newWikiPage->setVersion($wikiPage->getVersion() + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newWikiPage);
            $em->flush();
            $this->addFlash('notice', 'Updated wiki text');

            return $this->redirectToRoute('wiki_page', ['pageTitle' => $pageTitle]);
        }

        return $this->render(':wiki:edit_create.html.twig', [
            'title' => $pageTitle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/wiki/{pageTitle}/create", name="wiki_page_create")
     *
     * @param Request $request
     * @param $pageTitle
     *
     * @return Response
     */
    public function createWikiPageAction(Request $request, $pageTitle)
    {
        $wikiModel = $this->get('rox.wiki.model');
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
        if ($form->isValid() && $form->isSubmitted()) {
            $data = $form->getData();
            $newWikiPage = new Wiki();
            $newWikiPage->setPagename($wikiModel->getPagename($pageTitle));
            $newWikiPage->setVersion(1);
            $newWikiPage->setContent($data['wiki_markup']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newWikiPage);
            $em->flush();
            $this->addFlash('notice', 'Created wiki page');

            return $this->redirectToRoute('wiki_page', ['pageTitle' => $pageTitle]);
        }

        return $this->render(':wiki:edit_create.html.twig', [
            'title' => $pageTitle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/groups/{id}/wiki", name="group_wiki_page")
     *
     * @param Group $group
     *
     * @return Response
     */
    public function showGroupWikiPageAction(Group $group)
    {
        $member = $this->getUser();

        $wikiModel = $this->get('rox.wiki.model');
        $pageName = $wikiModel->getPageName('Group_'.$group->getName());

        $em = $this->getDoctrine();
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $em->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName);

        if (null === $wikiPage) {
            $output = 'No wiki found for this group. Creating not possible yet.';
        } else {
            $output = $wikiModel->parseWikiMarkup($wikiPage->getContent());
        }

        return $this->render(':group:wiki.html.twig', [
            'title' => 'Group '.$group->getName(),
            'submenu' => [
                'active' => 'wiki',
                'items' => $this->getGroupSubmenuItems($member, $group),
            ],
            'wikipage' => $output,
        ]);
    }

    /**
     * \todo move to group controller when the group controller is rewritten.
     *
     * @param Member $member
     * @param Group  $group
     *
     * @return array
     */
    private function getGroupSubmenuItems(Member $member, Group $group)
    {
        $groupId = $group->getId();
        $submenuItems = [
            'overview' => [
                'key' => 'GroupOverview',
                'url' => $this->generateUrl('group_start', ['group_id' => $groupId]),
            ],
            'forum' => [
                'key' => 'GroupDiscussions',
                'url' => $this->generateUrl('group_forum', ['group_id' => $groupId]),
            ],
            'wiki' => [
                'key' => 'GroupWiki',
                'url' => $this->generateUrl('group_wiki_page', ['id' => $groupId]),
            ],
            'members' => [
                'key' => 'GroupMembers',
                'url' => $this->generateUrl('group_members', ['group_id' => $groupId]),
            ],
        ];
        // \todo: Check if current user is member of this group
        if (\in_array($member, $group->getCurrentMembers(), true)) {
            $submenuItems['membersettings'] = [
                'key' => 'GroupMembersettings',
                'url' => $this->generateUrl('group_membersettings', ['group_id' => $groupId]),
            ];
            $submenuItems['relatedgroupsettings'] = [
                'key' => 'GroupRelatedGroups',
                'url' => $this->generateUrl('relatedgroup_log', ['group_id' => $groupId]),
            ];
        }

        return $submenuItems;
    }
}

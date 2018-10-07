<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Member;
use AppBundle\Entity\Wiki;
use AppBundle\Model\WikiModel;
use AppBundle\Repository\WikiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WikiController extends Controller
{
    /**
     * @Route("/wiki/{pageTitle}", name="wiki_page")
     *
     * @param $pageTitle
     *
     * @return Response
     */
    public function showWikiPageAction($pageTitle)
    {
        $wikiModel = new WikiModel();
        $pageName = $wikiModel->getPageName($pageTitle);

        $em = $this->getDoctrine();
        /** @var WikiRepository $wikiRepository */
        $wikiRepository = $em->getRepository(Wiki::class);

        $wikiPage = $wikiRepository->getPageByName($pageName);

        if (null === $wikiPage) {
            $output = 'No wiki page found with this name. Creating not possible yet.';
        } else {
            $output = $wikiModel->parseWikiMarkup($wikiPage->getContent());
        }

        return $this->render(':wiki:wiki.html.twig', [
            'title' => $pageTitle,
            'wikipage' => $output,
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

        $wikiModel = new WikiModel();
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
        if ($group->getMembers()->contains($member)) {
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

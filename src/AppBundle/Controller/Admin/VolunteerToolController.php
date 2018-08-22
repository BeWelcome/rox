<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Feedback;
use AppBundle\Entity\Member;
use AppBundle\Form\ChangeUsernameFormType;
use AppBundle\Form\CustomDataClass\Tools\ChangeUsernameRequest;
use AppBundle\Form\CustomDataClass\Tools\FindUserRequest;
use AppBundle\Form\FeedbackFormType;
use AppBundle\Form\FindUserFormType;
use AppBundle\Model\BaseModel;
use AppBundle\Model\FeedbackModel;
use AppBundle\Repository\MemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class VolunteerToolController extends Controller
{
    const CHANGE_USERNAME = 'admin.tools.change_username';
    const FIND_USER = 'admin.tools.find_user';
    const MESSAGES_LAST_WEEK = 'admin.tools.messages_last_week';
    const CHECK_FEEDBACK = 'admin.tools.check_feedback';
    const CHECK_SPAM_MESSAGES = 'admin.tools.check_spam_messages';
    const DAMAGE_DONE = 'admin.tools.damage_done';
    const AGE_BY_COUNTRY = 'admin.tools.age_by_country';

    /**
     * This directly redirects to the first assigned tool if any otherwise it redirects to the referrer page.
     *
     * @Route("/admin/tools", name="admin_volunteer_tools")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showOverviewAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $firstSubMenuItem = reset($subMenuItems);

        return $this->redirect($firstSubMenuItem['url']);
    }

    /**
     * @Route("/admin/tools/change", name="admin_tools_change_username")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeUsernameAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::CHANGE_USERNAME, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $form = $this->createForm(ChangeUsernameFormType::class, new ChangeUsernameRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $oldMember = $memberRepository->findOneBy(['username' => $data->oldUsername]);
            if (null !== $oldMember) {
                // check if new username is already taken
                $newMember = $memberRepository->findOneBy(['username' => $data->newUsername]);
                if (null === $newMember) {
                    $logger = $this->get('rox.logger');
                    $logger->write('Changed member username from '.$data->oldUsername.' to '.$data->newUsername.'.', 'adminquery', $this->getUser());

                    $em = $this->getDoctrine()->getManager();
                    $oldMember->setUsername($data->newUsername);
                    $em->persist($oldMember);
                    $em->flush();
                    $flashMessage = $this->get('translator')->trans('Changed username for %oldname% to %newname%', [
                        '%oldname%' => $data->oldUsername,
                        '%newname%' => $data->newUsername,
                    ]);
                    $this->addFlash('notice', $flashMessage);

                    return $this->redirectToRoute('admin_tools_change_username');
                }
                $form->get('newUsername')->addError(new FormError('A member with this username already exists. Please choose a different name.'));
            } else {
                $form->get('oldUsername')->addError(new FormError('No member with this username found.'));
                // check if new username is already taken
                $newMember = $memberRepository->findOneBy(['username' => $data->newUsername]);
                if (null !== $newMember) {
                    $form->get('newUsername')->addError(new FormError('A member with this username already exists. Please choose a different name.'));
                }
            }
        }

        return $this->render(
            ':admin:tools/change.username.html.twig',
            [
                'form' => $form->createView(),
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::CHANGE_USERNAME,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/findmember", name="admin_tools_find_user")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function findUserAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::FIND_USER, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $form = $this->createForm(FindUserFormType::class, new FindUserRequest());
        $form->handleRequest($request);

        $members = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $logger = $this->get('rox.logger');
            $logger->write('Searched for members using search term: '.$data->term.'.', 'adminquery', $this->getUser());

            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $members = $memberRepository->findByProfileInfo($data->term);
        }

        return $this->render(
            ':admin:tools/find.user.html.twig',
            [
                'form' => $form->createView(),
                'members' => $members,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::FIND_USER,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/check/feedback", name="admin_tools_check_feedback")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showSignupFeedbackAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::CHECK_FEEDBACK, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $types = $request->query->get('types', []);

        $feedbackModel = new FeedbackModel($this->getDoctrine());
        $categories = $feedbackModel->getCategories();

        $feedbackForm = $this->createForm(FeedbackFormType::class, [
            'categories' => $categories,
        ]);
        $feedbackForm->handleRequest($request);

        if ($feedbackForm->isSubmitted() && $feedbackForm->isValid()) {
            $data = $feedbackForm->getData();
            $types = $data['types'];
        }

        $feedbacks = $feedbackModel->getFilteredFeedback($types, $page, $limit);

        return $this->render(
            ':admin:tools/check.feedback.html.twig',
            [
                'form' => $feedbackForm->createView(),
                'feedbacks' => $feedbacks,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::CHECK_FEEDBACK,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/topspammer", name="admin_tools_top_spammer")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTopSpammerAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::CHECK_SPAM_MESSAGES, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        // Get all banned members with the number of sent messages for the last two months
        $baseModel = new BaseModel($this->getDoctrine());
        $messagesSent = $baseModel->execQuery("
            SELECT 
                COUNT(*) AS 'MessagesSent',
                Username AS Username,
                members.Status AS Status,
                members.updated AS Updated
            FROM
                members,
                messages
            WHERE
                messages.IdSender = members.id
                AND (members.Status = 'Banned'
                OR members.Status = 'Rejected')
                AND DATEDIFF(NOW(), members.Updated) < 91
            GROUP BY members.id
            ORDER BY members.updated DESC
            LIMIT 100;
        ")->fetchAll();

        /*        $damageDone = $baseModel->execQuery("select m1.Username as receiver,m1.Status,m1.updated 'LastUpdate',m2.Username Sender,m2.Status as 'Sender Status' from members as m1,members as m2,messages
        where messages.IdSender=m2.id and messages.IdReceiver=m1.id and m2.Status in ('Banned','Rejected') and m1.Status in ('TakenOut','AskToLeave') order  by m1.updated desc ,m1.id limit 40
        ");
        */
        return $this->render(
            ':admin:tools/top.spammer.html.twig',
            [
                'messagesSent' => $messagesSent,
            //                'damageDone' => $damageDone,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => 'admin.tools.top.spammer',
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/damagedone", name="admin_tools_damage_done")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showDamageDoneAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::DAMAGE_DONE, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        // Get all banned members with the number of sent messages for the last two months
        $baseModel = new BaseModel($this->getDoctrine());

        $damageDone = $baseModel->execQuery("
            SELECT 
                m1.Username AS 'Receiver',
                m1.Status AS 'ReceiverStatus',
                m1.updated 'LastUpdated',
                m2.Username AS 'Sender',
                m2.Status AS 'SenderStatus'
            FROM
                members AS m1,
                members AS m2,
                messages
            WHERE
                messages.IdSender = m2.id
                AND messages.IdReceiver = m1.id
                AND m2.Status IN ('Banned' , 'Rejected')
                AND m1.Status IN ('TakenOut' , 'AskToLeave')
            ORDER BY m2.Updated, m2.Username, m1.updated DESC , m1.id
            LIMIT 40
        ")->fetchAll();

        return $this->render(
            ':admin:tools/damage.done.html.twig',
            [
                'damageDone' => $damageDone,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => 'admin.tools.top.spammer',
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/messages/lastweek", name="admin_tools_messages_last_week")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showMessagesLastWeekAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::MESSAGES_LAST_WEEK, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $baseModel = new BaseModel($this->getDoctrine());
        $results = $baseModel->execQuery('
        SELECT
m.username AS Username,
g.name AS City,
g.country AS Country,
count(msg.id) AS Count
FROM
messages msg,
members m
LEFT JOIN geonames g ON m.IdCity = g.geonameID
WHERE
m.id = msg.IdSender
AND (DATE_ADD(msg.created,
    INTERVAL 3000 DAY) > NOW())
GROUP BY m.Username
HAVING COUNT(msg.id) > 500
ORDER BY count(msg.id) DESC')->fetchAll();

        return $this->render(
            ':admin:tools/messages.lastweek.html.twig',
            [
                'results' => $results,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => 'admin.tools.change.username',
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/countryage", name="admin_tools_age_by_country")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAverageAgePerCountryAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !array_key_exists(self::AGE_BY_COUNTRY, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $baseModel = new BaseModel($this->getDoctrine());
        $results = $baseModel->execQuery("
            SELECT 
                gc.Name AS Name,
                COUNT(*) AS Count,
                ROUND(AVG(m.BirthDate) / 10000) AS BirthYear,
                DATE_FORMAT(NOW(), '%Y') - ROUND(AVG(m.BirthDate) / 10000) AS 'Age'
            FROM
                members m,
                geonames g,
                geonamescountries gc
            WHERE
                m.Status = 'Active'
                AND m.IdCity = g.geonameId
                AND g.country = gc.country
            GROUP BY g.country
            ORDER BY 2 DESC;
        ")->fetchAll();

        return $this->render(
            ':admin/tools:age.country.html.twig',
            [
                'results' => $results,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::AGE_BY_COUNTRY,
                ],
            ]
        );
    }

    /**
     * @return array
     */
    private function getSubMenuItems()
    {
        $subMenu = [];
        if ($this->isGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_PROFILE])) {
            $subMenu[self::CHANGE_USERNAME] = [
                'key' => self::CHANGE_USERNAME,
                'url' => $this->generateUrl('admin_tools_change_username'),
            ];
        }
        if ($this->isGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_PROFILE, Member::ROLE_ADMIN_ACCEPTER])) {
            $subMenu[self::FIND_USER] = [
                'key' => self::FIND_USER,
                'url' => $this->generateUrl('admin_tools_find_user'),
            ];
        }
        if ($this->isGranted([Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_CHECKER])) {
            $subMenu[self::CHECK_FEEDBACK] = [
                'key' => self::CHECK_FEEDBACK,
                'url' => $this->generateUrl('admin_tools_check_feedback'),
            ];
        }
        if ($this->isGranted([Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER])) {
            $subMenu[self::CHECK_SPAM_MESSAGES] = [
                'key' => self::CHECK_SPAM_MESSAGES,
                'url' => $this->generateUrl('admin_tools_top_spammer'),
            ];
            $subMenu[self::DAMAGE_DONE] = [
                'key' => self::DAMAGE_DONE,
                'url' => $this->generateUrl('admin_tools_damage_done'),
            ];
        }
//        if ($this->isGranted([Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_CHECKER])) {
        $subMenu[self::AGE_BY_COUNTRY] = [
                'key' => self::AGE_BY_COUNTRY,
                'url' => $this->generateUrl('admin_tools_age_by_country'),
            ];
//        }
        if ($this->isGranted([Member::ROLE_ADMIN_PROFILE, Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ADMIN])) {
            $subMenu[self::MESSAGES_LAST_WEEK] = [
                'key' => self::MESSAGES_LAST_WEEK,
                'url' => $this->generateUrl('admin_tools_messages_last_week'),
                ];
        }

        return $subMenu;
    }
}

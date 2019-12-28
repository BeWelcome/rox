<?php

namespace App\Controller\Admin;

use App\Entity\Feedback;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\ChangeUsernameFormType;
use App\Form\CustomDataClass\Tools\ChangeUsernameRequest;
use App\Form\CustomDataClass\Tools\FindUserRequest;
use App\Form\FeedbackFormType;
use App\Form\FindUserFormType;
use App\Logger\Logger;
use App\Model\FeedbackModel;
use App\Repository\MemberRepository;
use App\Repository\MessageRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class VolunteerToolController.
 */
class VolunteerToolController extends AbstractController
{
    const CHANGE_USERNAME = 'admin.tools.change_username';
    const FIND_USER = 'admin.tools.find_user';
    const MESSAGES_LAST_WEEK = 'admin.tools.messages_last_week';
    const MESSAGES_BY_MEMBER = 'admin.tools.messages_by_member';
    const CHECK_FEEDBACK = 'admin.tools.check_feedback';
    const CHECK_TOP_SPAMMER = 'admin.tools.check_spam_messages';
    const DAMAGE_DONE = 'admin.tools.damage_done';
    const AGE_BY_COUNTRY = 'admin.tools.age_by_country';

    /** @var FeedbackModel */
    private $feedbackModel;

    public function __construct(FeedbackModel $feedbackModel)
    {
        $this->feedbackModel = $feedbackModel;
    }

    /**
     * This directly redirects to the first assigned tool if any otherwise it redirects to the referrer page.
     *
     * @Route("/admin/tools", name="admin_volunteer_tools")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showOverviewAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems)) {
            $this->addFlash('notice', 'flash.admin.tools.forbidden');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $firstSubMenuItem = reset($subMenuItems);

        return $this->redirect($firstSubMenuItem['url']);
    }

    /**
     * @Route("/admin/tools/change", name="admin_tools_change_username")
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     * @param Logger              $logger
     *
     * @throws Exception
     *
     * @return Response|RedirectResponse
     */
    public function changeUsernameAction(Request $request, TranslatorInterface $translator, Logger $logger)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::CHANGE_USERNAME, $subMenuItems)) {
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
                    $logger->write('Changed member username from '.$data->oldUsername.' to '.$data->newUsername.'.', 'adminquery');

                    $em = $this->getDoctrine()->getManager();
                    $oldMember->setUsername($data->newUsername);
                    $em->persist($oldMember);
                    $em->flush();
                    $flashMessage = $translator->trans('flash.admin.tools.changed', [
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
            'admin/tools/change.username.html.twig',
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
     * @param Logger  $logger
     *
     *@throws Exception
     *
     * @return Response|RedirectResponse
     */
    public function findUserAction(Request $request, Logger $logger)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::FIND_USER, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $form = $this->createForm(FindUserFormType::class, new FindUserRequest());
        $form->handleRequest($request);

        $members = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $logger->write('Searched for members using search term: '.$data->term.'.', 'adminquery');

            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $members = $memberRepository->findByProfileInfo($data->term);
        }

        return $this->render(
            'admin/tools/find.user.html.twig',
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
     * @return Response|RedirectResponse
     */
    public function showSignupFeedbackAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::CHECK_FEEDBACK, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $types = $request->query->get('types', []);

        $categories = $this->feedbackModel->getCategories();

        $feedbackForm = $this->createForm(FeedbackFormType::class, [
            'categories' => $categories,
        ]);
        $feedbackForm->handleRequest($request);

        if ($feedbackForm->isSubmitted() && $feedbackForm->isValid()) {
            $data = $feedbackForm->getData();
            $types = $data['types'];
        }

        $feedbacks = $this->feedbackModel->getFilteredFeedback($types, $page, $limit);

        return $this->render(
            'admin/tools/check.feedback.html.twig',
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
     * @throws Exception
     *
     * @return Response
     */
    public function showTopSpammerAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::CHECK_TOP_SPAMMER, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        // Get all banned members with the number of sent messages for the last two months
        $connection = $this->getDoctrine()->getConnection();

        $messagesSent = $connection->executeQuery("
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

        return $this->render(
            'admin/tools/top.spammer.html.twig',
            [
                'messagesSent' => $messagesSent,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::CHECK_TOP_SPAMMER,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/damagedone", name="admin_tools_damage_done")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function showDamageDoneAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::DAMAGE_DONE, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        // Get all banned members with the number of sent messages for the last two months
        $connection = $this->getDoctrine()->getConnection();

        $damageDone = $connection->executeQuery("
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
            'admin/tools/damage.done.html.twig',
            [
                'damageDone' => $damageDone,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::DAMAGE_DONE,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/messages/lastweek", name="admin_tools_messages_last_week")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function showMessagesLastWeekAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::MESSAGES_LAST_WEEK, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $connection = $this->getDoctrine()->getConnection();
        $results = $connection->executeQuery('
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
    INTERVAL 7 DAY) > NOW())
GROUP BY m.Username
HAVING COUNT(msg.id) > 9
ORDER BY count(msg.id) DESC')->fetchAll();

        return $this->render(
            'admin/tools/messages.lastweek.html.twig',
            [
                'results' => $results,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::MESSAGES_LAST_WEEK,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/messages/member", name="admin_tools_messages_by_member")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function showMessagesByMember(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();

        if (empty($subMenuItems) | !\array_key_exists(self::MESSAGES_BY_MEMBER, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $usernameForm = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'member-autocomplete',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('submit', SubmitType::class)
            ->setMethod('POST')
            ->getForm();
        $usernameForm->handleRequest($request);

        $results = [];
        $member = null;
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $data = $usernameForm->getData();
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            /** @var Member $member */
            $member = $memberRepository->findOneBy(['username' => $data['username']]);

            /** @var MessageRepository $messageRepository */
            $messageRepository = $this->getDoctrine()->getRepository(Message::class);
            $messages = $messageRepository->findAllMessagesWithMember($member);

            // Work through all messages and create list of members involved
            foreach($messages as $message) {
                $sender = $message->getSender();
                $receiver = $message->getReceiver();
                $correspondent = ($sender === $member) ? $receiver : $sender;
                $username = $correspondent->getUsername();
                if (!array_key_exists($username, $results)) {
                    $results[$username] = [
                        'username' => $username,
                        'direction' => 0,
                        'last_sent' => DateTime::createFromFormat ( 'Y-m-d H:i:s', '1900-01-01 00:00:00'),
                        'last_received' => DateTime::createFromFormat ( 'Y-m-d H:i:s', '1900-01-01 00:00:00'),
                    ];
                }
                $result = $results[$username];
                if ($sender !== $member) {
                    $result['direction'] = $result['direction'] | 1;
                    if ($message->getCreated() > $result['last_received']) {
                        $result['last_received'] = $message->getCreated();
                    }
                } else {
                    $result['direction'] |= $result['direction'] | 2;
                    if ($message->getCreated() > $result['last_sent']) {
                        $result['last_sent'] = $message->getCreated();
                    }
                }
                $results[$username] = $result;
            }
        }

        return $this->render(
            'admin/tools/messages.by.member.html.twig',
            [
                'form' => $usernameForm->createView(),
                'member' => $member,
                'results' => $results,
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => self::MESSAGES_BY_MEMBER,
                ],
            ]
        );
    }

    /**
     * @Route("/admin/tools/countryage", name="admin_tools_age_by_country")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function showAverageAgePerCountryAction(Request $request)
    {
        // check permissions
        $subMenuItems = $this->getSubMenuItems();
        if (empty($subMenuItems) | !\array_key_exists(self::AGE_BY_COUNTRY, $subMenuItems)) {
            $this->addFlash('notice', 'admin.tools.not.allowed');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $connection = $this->getDoctrine()->getConnection();
        $results = $connection->executeQuery("
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
            'admin/tools/age.country.html.twig',
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
            $subMenu[self::CHECK_TOP_SPAMMER] = [
                'key' => self::CHECK_TOP_SPAMMER,
                'url' => $this->generateUrl('admin_tools_top_spammer'),
            ];
            $subMenu[self::DAMAGE_DONE] = [
                'key' => self::DAMAGE_DONE,
                'url' => $this->generateUrl('admin_tools_damage_done'),
            ];
        }
        if ($this->isGranted([Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_CHECKER])) {
            $subMenu[self::AGE_BY_COUNTRY] = [
                'key' => self::AGE_BY_COUNTRY,
                'url' => $this->generateUrl('admin_tools_age_by_country'),
            ];
        }
        if ($this->isGranted([Member::ROLE_ADMIN_PROFILE, Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ADMIN])) {
            $subMenu[self::MESSAGES_LAST_WEEK] = [
                'key' => self::MESSAGES_LAST_WEEK,
                'url' => $this->generateUrl('admin_tools_messages_last_week'),
            ];
        }
        if ($this->isGranted([Member::ROLE_ADMIN_SAFETYTEAM])) {
            $subMenu[self::MESSAGES_BY_MEMBER] = [
                'key' => self::MESSAGES_BY_MEMBER,
                'url' => $this->generateUrl('admin_tools_messages_by_member'),
            ];
        }

        return $subMenu;
    }
}

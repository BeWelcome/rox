<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\FeedbackCategory;
use AppBundle\Entity\Member;
use AppBundle\Form\CustomDataClass\ReportCommentRequest;
use AppBundle\Form\ReportCommentType;
use AppBundle\Repository\MemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    /**
     * @Route("/member/autocomplete", name="members_autocomplete")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $names = [];
        $callback = trim(strip_tags($request->get('callback')));
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        $entities = $memberRepository->loadMembersByUsernamePart($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity['username'],
                'label' => $entity['username'],
                'value' => $entity['username'],
            ];
        }

        $response = new JsonResponse();
        $response->setCallback($callback);
        $response->setData($names);

        return $response;
    }

    /**
     * @Route("/members/{username}/comment/{commentId}/report", name="report_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     *
     * @ParamConverter("member", class="AppBundle\Entity\Member", options={"mapping": {"username": "username"}})
     * @ParamConverter("comment", class="AppBundle\Entity\Comment", options={"mapping": {"commentId": "id"}})
     *
     * @param Request $request
     * @param Member  $member
     * @param Comment $comment
     *
     * @return Response
     */
    public function reportCommentAction(Request $request, Member $member, Comment $comment)
    {
//        \todo Should we only allow the receiver of a comment to report it?
//        if ($comment->getToMember()->getId() !== $member->getId() && $comment->getFromMember()->getId() !== $member->getId()) {
//            throw new AccessDeniedException('Hau ab!');
//        }

        $user = $this->getUser();

        $form = $this->createForm(ReportCommentType::class, new ReportCommentRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $feedback = trim(str_replace("\xc2\xa0", ' ', strip_tags(html_entity_decode($data->feedback, ENT_HTML5, 'UTF-8'))));
            if (empty($feedback)) {
                $form->addError(new FormError('Feedback can not be empty.'));
            } else {
                $messageText = $this->render(':emails:comment.feedback.html.twig', [
                    'comment' => $comment,
                    'feedback' => $feedback,
                ]);
                // Get the email address that is associated with admin comments category
                $feedbackCategoryRepository = $this->getDoctrine()->getRepository(FeedbackCategory::class);
                $feedbackCategory = $feedbackCategoryRepository->findOneBy(['name' => 'Comment_issue']);

                $message = (new Swift_Message())
                    ->setSubject('Comment report')
                    ->setFrom(
                        [
                            $user->getEmail() => 'BeWelcome - '.$user->getUsername(),
                        ]
                    )
                    ->setTo([
                        $feedbackCategory->getEmailToNotify() => 'Comment Issue',
                    ])
                    ->setBody(
                        $messageText,
                        'text/html'
                    );
                $recipients = $this->get('mailer')->send($message);
                if (0 === $recipients) {
                    $this->addFlash('error', 'Your feedback couldn\'t be sent. Please try again later.');
                } else {
                    $this->addFlash('notice', 'Your feedback has been forwarded to the Safety Team.');

                    return $this->redirectToRoute('profile_all_comments', ['username' => $member->getUsername()]);
                }
            }
        }

        return $this->render(':member:report.comment.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'member' => $member,
        ]);
    }
}

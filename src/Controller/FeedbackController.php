<?php

namespace App\Controller;

use App\Form\FeedbackFormType;
use App\Model\AboutModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Carbon\Carbon;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackController extends AboutBaseController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /**
     * @Route("/about/feedback", name="contactus")
     * @Route("/contact")
     * @Route("/contactus")
     * @Route("/support")
     * @Route("/feedback", name="feedback")
     *
     * @return Response|RedirectResponse
     */
    public function feedback(Request $request, AboutModel $aboutModel, TranslatorInterface $translator)
    {
        $noModal = $request->query->get('no', false);

        $member = $this->getUser();
        $categories = $aboutModel->getFeedbackCategories();
        $categoryId = $request->get('IdCategory', null);
        $category = (null !== $categoryId) ? $categories[$categoryId] : null;
        $messageId = $request->get('messageId', null);
        $username = $request->get('username', null);
        $feedbackQuestion = '';
        if (null !== $username) {
            $feedbackQuestion = $translator->trans('profile.report.text');
            $feedbackQuestion = sprintf($feedbackQuestion, $username);
        }
        $form = $this->createForm(
            FeedbackFormType::class,
            [
                'IdCategory' => $category,
                'FeedbackQuestion' => $feedbackQuestion,
            ],
            [
                'categories' => $categories,
                'member' => $member,
                'csrf_protection' => false,
                'allow_extra_fields' => true,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['FeedbackQuestion'] === $feedbackQuestion) {
                $form->get('FeedbackQuestion')->addError(
                    new FormError(
                        $translator->trans('feedback.missing.info', [], 'validators')
                    )
                );
            }

            $reply = !($data['no_reply_needed']);
            if ($reply && null === $data['FeedbackEmail']) {
                $form
                    ->addError(
                        new FormError(
                            $translator->trans('form.error.feedback.email.needed', [], 'validators')
                        )
                    )
                ;
            }
            // Recheck validity as we might have set an error before.
            if ($form->isValid()) {
                $data['member'] = $member;
                $data['browser'] = $request->headers->get('User-Agent');
                $data['version'] = 'no version set';
                if (file_exists('../VERSION')) {
                    $version = trim(file_get_contents('../VERSION'));
                    $versionCreated = Carbon::createFromTimestamp(filemtime('../VERSION'));
                    $data['version'] = $version . ' (' . $versionCreated . ')';
                }

                $data['host'] = $request->headers->get('Host');
                $aboutModel->sendFeedbackEmail($data);
                $aboutModel->addFeedback($data);

                $this->addTranslatedFlash('notice', 'feedback.thank.you');

                return $this->redirectToRoute('feedback_received');
            }
        }

        return $this->render('about/feedback.html.twig', [
            'form' => $form->createView(),
            'no_modal' => $noModal,
            'messageId' => $messageId,
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_feedback',
            ],
        ]);
    }

    /**
     * @Route("/feedback/received", name="feedback_received")
     *
     * @return Response
     */
    public function feedbackReceived(Request $request)
    {
        return $this->render('about/feedback.received.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_feedback',
            ],
        ]);
    }
}

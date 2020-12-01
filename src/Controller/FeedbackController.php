<?php

namespace App\Controller;

use App\Form\FeedbackFormType;
use App\Model\AboutModel;
use Carbon\Carbon;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackController extends AboutBaseController
{
    /**
     * @Route("/about/feedback", name="contactus")
     *
     * @return RedirectResponse
     */
    public function showAboutFeedback(Request $request)
    {
        return $this->redirectToRoute('feedback');
    }

    /**
     * @Route("/feedback", name="feedback")
     *
     * @return Response|RedirectResponse
     */
    public function feedback(Request $request, AboutModel $aboutModel, TranslatorInterface $translator)
    {
        $member = $this->getUser();
        $categories = $aboutModel->getFeedbackCategories();
        $form = $this->createForm(
            FeedbackFormType::class,
            null,
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
            $reply = !($data['no_reply_needed']);
            if ($reply && null === $data['FeedbackEmail']) {
                $form
                    ->addError(
                        new FormError(
                            $translator->trans('form.error.feedback.email.needed', [], 'validators')
                        )
                    )
                ;
            } else {
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

                return $this->redirectToRoute('feedback_received');
            }
        }

        return $this->render('about/feedback.html.twig', [
            'form' => $form->createView(),
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

<?php

namespace App\Controller\Admin;

use App\Form\CheckerIndexFormType;
use App\Model\MessageModel;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckerController extends AbstractController
{
    /**
     * @Route("/admin/spamchecker", name="admin_spam_overview")
     *
     * @param Request      $request
     * @param MessageModel $messageModel
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return Response
     */
    public function showOverview(Request $request, MessageModel $messageModel)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 2);

        $reportedMessages = $messageModel->getReportedMessages($page, $limit);
        $messageIds = [];
        foreach ($reportedMessages->getIterator() as $key => $val) {
            $messageIds[$key] = $val->getId();
        }

        $form = $this->createForm(CheckerIndexFormType::class, null, [
            'ids' => $messageIds,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $spamMessageIds = $data['spamMessages'];
            $noSpamMessageIds = $data['noSpamMessages'];
            $ids = array_intersect($spamMessageIds, $noSpamMessageIds);
            if (!empty($ids)) {
                $form->addError(new FormError('Spam and no spam are mutually exclusive'));
            } else {
                if (!empty($spamMessageIds)) {
                    $messageModel->markAsSpamByChecker($spamMessageIds);
                }
                if (!empty($noSpamMessageIds)) {
                    $messageModel->unmarkAsSpamByChecker($noSpamMessageIds);
                }
                $this->addFlash('notice', 'Set spam status');

                return $this->redirectToRoute('admin_spam_overview');
            }
        }

        return  $this->render('admin/checker/index.html.twig', [
            'form' => $form->createView(),
            'reported' => $reportedMessages,
        ]);
    }
}

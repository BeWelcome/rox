<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\LogFormType;
use App\Model\LogModel;
use App\Repository\MemberRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    /**
     * @Route("/admin/logs", name="admin_logs_overview")
     *
     * @param Request  $request
     * @param LogModel $logModel
     *
     * @throws NonUniqueResultException
     *
     * @return Response
     */
    public function showOverview(Request $request, LogModel $logModel)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_LOGS)) {
            throw $this->createAccessDeniedException('You need to have Logs right to access this.');
        }

        $member = null;
        /** @var Member $logViewer */
        $logViewer = $this->getUser();
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $types = $request->query->get('log.types', []);
        $username = $request->query->get('log[username]', null);

        $logTypes = $logModel->getLogTypes($logViewer);
        if (count($logTypes) == 1) {
            $request->query->set('types', $logTypes);
            $types = $logTypes;
        }
        if (empty($types)) {
            $types = $logTypes;
        }
        $logForm = $this->createForm(LogFormType::class, [
            'logTypes' => $logTypes,
        ]);
        $logForm->handleRequest($request);

        if ($logForm->isSubmitted() && $logForm->isValid()) {
            $data = $logForm->getData();
            $types = $data['types'];
            $username = $data['username'];
        }
        if (!empty($username)) {
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $member = $memberRepository->loadUserByUsername($username);
        }

        $logs = $logModel->getFilteredLogs($types, $member, $page, $limit);

        return  $this->render('admin/logs/index.html.twig', [
            'form' => $logForm->createView(),
            'logs' => $logs,
        ]);
    }

    /**
     * @Route("/admin/logs/groups", name="admin_groups_logs")
     *
     * @param Request  $request
     * @param LogModel $logModel
     *
     * @return Response
     */
    public function showGroupLogs(Request $request, LogModel $logModel)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FORUMMODERATOR)) {
            throw $this->createAccessDeniedException('You need to have Logs right to access this.');
        }

        $member = null;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $logs = $logModel->getFilteredLogs(['Group'], null, $page, $limit);

        return  $this->render('admin/logs/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}

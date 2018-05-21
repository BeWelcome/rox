<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Member;
use AppBundle\Form\LogFormType;
use AppBundle\Model\LogModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LogController extends Controller
{
    /**
     * @Route("/admin/logs", name="admin_logs_overview")
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showOverview(Request $request)
    {
        $member = null;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $logModel = new LogModel($this->getDoctrine());
        $logTypes = $logModel->getLogTypes();

        $logForm = $this->createForm(LogFormType::class, [
            'logTypes' => $logTypes,
        ]);
        $logForm->handleRequest($request);

        $types = [];
        $member = null;
        $ipAddress = null;
        if ($logForm->isSubmitted() && $logForm->isValid()) {
            $data = $logForm->getData();
            $types = $data['types'];
            $username = $data['username'];
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            $member = $memberRepository->loadUserByUsername($username);
            $ipAddress = $data['ipaddress'];
        }

        $logs = $logModel->getFilteredLogs($types, $member, $ipAddress, $page, $limit);

        return  $this->render(':admin:logs/index.html.twig', [
            'form' => $logForm->createView(),
            'logs' => $logs,
        ]);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Member;
use AppBundle\Form\CustomDataClass\GroupRequest;
use AppBundle\Form\CustomDataClass\SearchFormRequest;
use AppBundle\Form\GroupType;
use AppBundle\Form\SearchFormType;
use AppBundle\Pagerfanta\SearchAdapter;
use AppBundle\Repository\GroupRepository;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * @Route("/groups/new", name="new_group")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createNewGroupAction(Request $request)
    {
        $groupRequest = new GroupRequest();
        $form = $this->createForm(GroupType::class, $groupRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $member = $this->getUser();
            // if a file was uploaded move it into the image storage
            /** @var UploadedFile $file */
            $file = $data->picture;
            $fileName = '';
            if (null !== $file)
            {
                $fileName = 'hallo'; // $this->generateUniqueFileName().'.'.$file->guessExtension();

                // moves the file to the directory where brochures are stored
                $file->move(
                    $this->getParameter('group_directory'),
                    $fileName
                );
            }
            $group = new Group();
            $group->setName($data->name);
            $group->setType($data->type);
            $group->setVisibleposts($data->membersOnly);
            $group->setVisiblecomments(false);
            $group->setIdDescription(0 );
            $group->setMoreinfo('');
            $group->setPicture($fileName);
            $group->addMember($member);

            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            $this->addFlash('notice', 'The group was created and is now awaiting approval. You get a notification with the result.');

            // Get all group admins and send them a notification
            // \todo
            return $this->redirectToRoute('groups_overview');
        }

        return $this->render(':group:create.group.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/groups/new/check", name="new_group_check")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxCheckNewGroupAction(Request $request)
    {
        $groupName = trim($request->request->get('name'));

        $html = '';
        if (!empty($groupName))
        {
            $parts = explode(' ', $groupName);

            /** @var GroupRepository $groupRepository */
            $groupRepository = $this->getDoctrine()->getRepository(Group::class );
            $groups = $groupRepository->findByNameParts($parts);

            // Check if there are duplicate groups and provide a list of these

            $html = $this->renderView(':group:check.group.html.twig', [
                'groups' => $groups
            ]);
        }
        return new JsonResponse([
            'html' => $html,
        ]);
    }

    /**
     * Allows to approved or dismiss group creation requests
     *
     * @Route("/admin/groups/approval", name="admin_groups_approval")
     *
     * @param Request $request
     * @return Response
     */
    public function approveGroupsAction(Request $request)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        // Fetch unapproved groups and decide on their fate
        $groupsRepository = $this->getDoctrine()->getRepository(Group::class);
        $groups = $groupsRepository->findBy([ 'approved' => Group::NOT_APPROVED]);

        return $this->render( ':admin:groups/approve.html.twig', [
            'groups' => $groups
        ]);
    }

    /**
     * Dismiss a group creation requests
     *
     * @Route("/admin/groups/{id}/dismiss", name="admin_groups_dismiss")
     *
     * @param Request $request
     * @param Group $group
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dismissGroupAction(Request $request, Group $group)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $group->setApproved(Group::DISMISSED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $flashMessage = $this->get('translator')->trans('Dismissed group %name%', [
            '%name%' => $group->getName(),
        ]);

        $this->addFlash('notice', $flashMessage);

        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }

    /**
     * Dismiss a group creation requests
     *
     * @Route("/admin/groups/{id}/approve", name="admin_groups_approve")
     *
     * @param Request $request
     * @param Group $group
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function approveGroupAction(Request $request, Group $group)
    {
        if (!$this->isGranted([Member::ROLE_ADMIN_GROUP])) {
            throw $this->createAccessDeniedException('You need to have Group right to access this.');
        }

        $group->setApproved(Group::APPROVED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $flashMessage = $this->get('rox.datacollector_translator')->trans('Approved creation for group %name% ', [
            '%name%' => $group->getName(),
        ]);
        $this->addFlash('notice', $flashMessage);

        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}

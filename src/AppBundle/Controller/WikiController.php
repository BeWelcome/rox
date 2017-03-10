<?php

namespace AppBundle\Controller;


use AppBundle\Form\WikiCreateForm;
use Exception;
use RemoteAPI;
use RemoteAPICore;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WikiController extends Controller
{
    /**
     * @Route("/wiki/", name="wiki_main")
     * @Route("/wiki/{page}", name="wiki_page")
     *
     * @param string $page
     * @return Response
     */
    function wikiShowAction(Request $request, $page = 'start')
    {
        $dokuwikiDirectory = $this->getParameter("dokuwiki_directory");
        require $dokuwikiDirectory . '/inc/init.php';

        $remoteApiCore = new RemoteApiCore(new RemoteAPI());

        // First check if the page already exists
        $notFound = false;
        try {
            $remoteApiCore->pageInfo($page);
        }
        catch(Exception $e)
        {
            $notFound = true;
        }
        if ($notFound) {
            return $this->redirectToRoute('wiki_page_create', [ 'page' => $page ]);
        }

        $htmlPage = $remoteApiCore->htmlPage($page);

        $createForm = $this->createForm(WikiCreateForm::class, [ 'wikipage' => $htmlPage]);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $data = $createForm->getData();
            $remoteApiCore->putPage($page, $data['wikipage'], []);
        }

        return $this->render(':wiki:create.html.twig', [
            'form' => $createForm->createView(),
        ]);
    }

    /**
     * @Route("/wiki/{page}/create", name="wiki_page_create")
     *
     * @param Request $request
     * @param $page
     * @return Response
     */
    function wikiCreateAction(Request $request, $page)
    {
        $dokuwikiDirectory = $this->getParameter("dokuwiki_directory");
        require $dokuwikiDirectory . '/inc/init.php';

        $remoteApiCore = new RemoteApiCore(new RemoteAPI());

        // First check if the page already exists
        $found = true;
        try {
            $remoteApiCore->pageInfo($page);
        }
        catch(Exception $e)
        {
            $found = false;
        }
        if ($found) {
            return $this->redirectToRoute('wiki_page', [ 'page' => $page ]);
        }

        $createForm = $this->createForm(WikiCreateForm::class);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $data = $createForm->getData();
            $remoteApiCore->putPage($page, $data['wikipage'], []);

            return $this->redirectToRoute('wiki_page', [ 'page' => $page ]);
        }

        return $this->render(':wiki:create.html.twig', [
            'form' => $createForm->createView()
        ]);
    }
}
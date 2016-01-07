<?php

namespace Rox\Admin\Queries;

use Rox\Framework\Controller;
use Rox\Models\Query;
use Rox\Models\Member;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Admin Queries controller
 *
 * @package Admin
 * @author shevek
 */
class QueriesController extends Controller
{

    /**
     * @return Response
     */
    public function showOverview(Request $request) {
        $form = $this->createForm(AdminQueryType::class);
        $form->handleRequest($request);
        $page = new AdminQueriesOverviewPage($this->getRouting());
        $page->initializeFormComponent(false);
        $page->addForm($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->addParameters(['results' => 'These are the results.']);
        }
        return new Response($page->render());
    }
}
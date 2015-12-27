<?php

namespace Rox\Admin\Queries;

use Rox\Models\Queries;
use Rox\Models\Member;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
class QueriesController extends \RoxControllerBase
{

    /**
     * @var \RoxModelBase
     */
    private $_model;

    /**
     * QueriesController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_model = new \RoxModelBase();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    /**
     * @return Response
     */
    public function showOverview(Request $request) {
        $form = $this->formFactory->createBuilder()
            ->add('choice', TextType::class, array(
                'required' => false,
                'constraints' => new NotBlank(),
            ))
            ->add('task', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array( 'min' => 3, 'max' => 10))
            )))
            ->add('dueDate', DateType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Type('\DateTime'),
                )
            ))
            ->getForm();

        $form->handleRequest($request);
        $page = new AdminQueriesOverviewPage($this->routing);
        $page->addForm($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->addParameters(['results' => 'These are the results.']);
        }
        return new Response($page->render());
    }
}
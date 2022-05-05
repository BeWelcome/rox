<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\PasswordFormType;
use App\Logger\Logger;
use App\Model\MemberModel;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class MemberController.
 */
class MockupController extends AbstractController
{
    /**
     * @Route("/mockup/menus/{number}", name="mockup_menus")
     */
    public function menuTest(int $number = 1): Response
    {
        $next = $number + 1;
        if ($next == 4) $next = 1;
        return $this->render('mockup/menus.html.twig', [
            'template' => 'mockup/menu' . $number . '.html.twig',
            'menu_url' => 'mockup_menus',
            'next' => $next,
        ]);
    }
}

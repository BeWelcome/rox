<?php

namespace App\Tests\Controller;

use App\Controller\SubscriptionController;
use App\Model\SubscriptionModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionControllerTest extends TestCase
{
    private const KEY = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    /** @var SubscriptionController&MockObject */
    private $controller;

    /** @var SubscriptionModel&MockObject */
    private $subscriptionModel;

    public function setUp(): void
    {
        $form = $this->createStub(FormInterface::class);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('isSubmitted')->willReturn(false);
        $form->method('createView')->willReturn(new FormView());

        // the confirmation page needs the form and Twig; replace both, the one-click path uses neither
        $this->controller = $this->getMockBuilder(SubscriptionController::class)
            ->onlyMethods(['createForm', 'render'])
            ->getMock();
        $this->controller->method('createForm')->willReturn($form);
        $this->controller->method('render')->willReturn(new Response('confirmation page'));

        $this->subscriptionModel = $this->createMock(SubscriptionModel::class);
    }

    public function testOneClickPostUnsubscribes()
    {
        // what Gmail, Yahoo and Apple Mail send (RFC 8058)
        $request = Request::create('/unsubscribe/newsletter/member/' . self::KEY, 'POST', [
            'List-Unsubscribe' => 'One-Click',
        ]);

        $this->subscriptionModel->expects($this->once())
            ->method('unsubscribeNewsletter')
            ->with('member', self::KEY)
            ->willReturn(true);

        $response = $this->controller->unsubscribeNewsletter($request, $this->subscriptionModel, 'member', self::KEY);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testLinkInTheMailShowsTheConfirmationPage()
    {
        $request = Request::create('/unsubscribe/newsletter/member/' . self::KEY, 'GET', [
            'List-Unsubscribe' => 'One-Click',
        ]);

        $this->subscriptionModel->expects($this->never())->method('unsubscribeNewsletter');

        $response = $this->controller->unsubscribeNewsletter($request, $this->subscriptionModel, 'member', self::KEY);

        $this->assertSame('confirmation page', $response->getContent());
    }

    public function testPostWithoutOneClickShowsTheConfirmationPage()
    {
        $request = Request::create('/unsubscribe/newsletter/member/' . self::KEY, 'POST', [
            'List-Unsubscribe' => 'List-Unsubscribe=One-Click',
        ]);

        $this->subscriptionModel->expects($this->never())->method('unsubscribeNewsletter');

        $response = $this->controller->unsubscribeNewsletter($request, $this->subscriptionModel, 'member', self::KEY);

        $this->assertSame('confirmation page', $response->getContent());
    }
}

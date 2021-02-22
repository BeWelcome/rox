<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext;
use Behatch\Context\RestContext as BehatchRestContext;

final class RestContext implements Context
{
    private ?BehatchRestContext $restContext = null;
    private ?MinkContext $minkContext = null;
    private ?JsonContext $jsonContext = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->restContext = $environment->getContext(BehatchRestContext::class);
        $this->minkContext = $environment->getContext(MinkContext::class);
        $this->jsonContext = $environment->getContext(JsonContext::class);
    }

    /**
     * @Then I should be unauthorized
     */
    public function unauthorized(): void
    {
        $this->minkContext->assertResponseStatus(401);
    }

    /**
     * @Then I should be forbidden
     */
    public function forbidden(): void
    {
        $this->minkContext->assertResponseStatus(403);
    }

    /**
     * @When I should see the following errors:
     */
    public function iSeeTheFollowingErrors(TableNode $errors): void
    {
        $this->minkContext->assertResponseStatus(422);
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theResponseShouldBeInJson();
        foreach ($errors as $error) {
            $this->jsonContext->theJsonNodeShouldContain('hydra:description', $error);
        }
    }
}

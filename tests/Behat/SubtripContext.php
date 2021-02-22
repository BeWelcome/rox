<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext;
use Behatch\Context\RestContext;
use DateTimeImmutable;

/**
 * @author Vincent Chalamon <vincent.chalamon@ext.arte.tv>
 */
final class SubtripContext implements Context
{
    private ?RestContext $restContext = null;
    private ?MinkContext $minkContext = null;
    private ?JsonContext $jsonContext = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->restContext = $environment->getContext(RestContext::class);
        $this->minkContext = $environment->getContext(MinkContext::class);
        $this->jsonContext = $environment->getContext(JsonContext::class);
    }

    /**
     * @When I create a subtrip for Berlin in :start days for :duration days meeting locals and looking for a host
     */
    public function createSubtripMeetingLocalsLookingForAHost(int $start, int $duration): void
    {
        $this->createSubtrip($start, $duration, ['MeetLocals', 'LookingForHosts']);
    }

    /**
     * @When I create a subtrip for Berlin in :start days for :duration days meeting locals
     */
    public function createSubtripMeetingLocals(int $start, int $duration): void
    {
        $this->createSubtrip($start, $duration, ['MeetLocals']);
    }

    /**
     * @When I create a subtrip for Berlin in :start days for :duration days looking for a host
     */
    public function createSubtripLookingForAHost(int $start, int $duration): void
    {
        $this->createSubtrip($start, $duration, ['LookingForHosts']);
    }

    /**
     * @When I create a subtrip for Berlin in :start days for :duration days
     */
    public function createSubtrip(int $start, int $duration, array $options = []): void
    {
        $start = new DateTimeImmutable("$start days");
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/ld+json');
        $this->restContext->iSendARequestToWithBody('POST', '/api/subtrips', new PyStringNode([sprintf(
            <<<JSON
{
  "location": "/api/locations/2921044",
  "arrival": "%s",
  "departure": "%s",
  "options": [%s],
  "trip": "/api/trips/1"
}
JSON
            , $start->format('Y-m-d')
            , $start->modify("$duration days")->format('Y-m-d')
            , implode(', ', $options)
        )], 0));
    }

    /**
     * @When the subtrip should have been successfully created
     */
    public function checkCreateSubtripResponse(): void
    {
        $this->minkContext->assertResponseStatus(201);
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToTheSchema('tests/Behat/json/subtrip/item/schema.json');
    }

    /**
     * @When I create a subtrip for Berlin on another member's trip
     */
    public function createInvalidSubtrip(): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/ld+json');
        $this->restContext->iSendARequestToWithBody('POST', '/api/subtrips', new PyStringNode([sprintf(
            <<<JSON
{
  "location": "/api/locations/2921044",
  "arrival": "%s",
  "departure": "%s",
  "options": ["MeetLocals", "LookingForHosts"],
  "trip": "/api/trips/2"
}
JSON
            , (new DateTimeImmutable('2 days'))->format('Y-m-d')
            , (new DateTimeImmutable('4 days'))->format('Y-m-d')
        )], 0));
    }

    /**
     * @When I get a subtrip for :city
     */
    public function getSubtrip(string $city): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iSendARequestTo('GET', [
            'Berlin' => '/api/subtrips/1',
            'Jayapura' => '/api/subtrips/2',
            'Munich' => '/api/subtrips/3',
        ][$city]);
    }

    /**
     * @When I should see the subtrip
     */
    public function checkSubtripResponse(): void
    {
        $this->minkContext->assertResponseStatus(200);
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonShouldBeValidAccordingToTheSchema('tests/Behat/json/subtrip/item/schema.json');
    }

    /**
     * @When I update my subtrip for Berlin
     */
    public function updateSubtrip(): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/ld+json');
        $this->restContext->iSendARequestToWithBody('PUT', '/api/subtrips/1', new PyStringNode([sprintf(
            <<<JSON
{
  "arrival": "%s",
  "departure": "%s"
}
JSON
            , (new DateTimeImmutable('3 days'))->format('Y-m-d')
            , (new DateTimeImmutable('6 days'))->format('Y-m-d')
        )], 0));
    }

    /**
     * @When I update the subtrip for Munich of another member's trip
     */
    public function updateInvalidSubtrip(): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/ld+json');
        $this->restContext->iSendARequestToWithBody('PUT', '/api/subtrips/3', new PyStringNode([<<<JSON
{
  "location": "/api/locations/2921044",
  "arrival": "2021-02-19",
  "departure": "2021-02-21",
  "options": ["MeetLocals", "LookingForHosts"]
}
JSON
        ], 0));
    }

    /**
     * @When I delete my subtrip for Berlin
     */
    public function deleteSubtrip(): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iSendARequestTo('DELETE', '/api/subtrips/1');
    }

    /**
     * @When I delete the subtrip for Munich of another member's trip
     */
    public function deleteInvalidSubtrip(): void
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iSendARequestTo('DELETE', '/api/subtrips/3');
    }

    /**
     * @When the subtrip should have been successfully deleted
     */
    public function checkDeleteSubtripResponse(): void
    {
        $this->minkContext->assertResponseStatus(204);
        $this->restContext->theResponseShouldBeEmpty();
    }
}

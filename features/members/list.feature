Feature: Members list
  In order to find a member
  As a member
  I must be able to list and search for members

  Scenario: As anonymous, I cannot list members
    When I send a "GET" request to "/api/members"
    Then the response status code should be 401

  Scenario: As a member, I can list active and verified members
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/member/collection/members.json"

  Scenario Outline: As a member, I can search for a valid member
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members?username=<username>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/member/collection/search.json"
    Examples:
      | username |
      | member-2 |
      | member-3 |
      | member-4 |
      | member-5 |

  Scenario Outline: As a member, I cannot search for a banned or invalid member
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members?username=<username>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/member/collection/empty.json"
    Examples:
      | username         |
      | member-banned    |
      | member-taken-out |
      | invalid          |

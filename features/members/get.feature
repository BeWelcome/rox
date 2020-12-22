Feature: Members get
  In order to read a profile
  As a member
  I must be able to get a member

  Scenario: As anonymous, I cannot get a member
    When I send a "GET" request to "/api/members/member-2"
    Then the response status code should be 401

  Scenario: As a member, I can get my account
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members/member-2"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/member/item/member-2.json"

  Scenario Outline: As a member, I cannot get a banned or invalid member
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members/<username>"
    Then the response status code should be 404
    Examples:
      | username         |
      | member-banned    |
      | member-taken-out |
      | member-invalid   |

  Scenario: As a member, I cannot get a valid member by its identifier
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members/3"
    Then the response status code should be 404

  Scenario: As a member, I can get any member by its username
    Given I am authenticated as "member-2"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/members/member-3"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/member/item/member-3.json"

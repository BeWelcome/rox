Feature: Members list
  For security reasons,
  As anonymous or as a member,
  I cannot list or search for members

  Scenario: As anonymous, I cannot list members
    When I send a "GET" request to "/api/members"
    Then the response status code should be 404

  Scenario: As a member, I cannot list members
    Given I am authenticated as "member-2"
    When I send a "GET" request to "/api/members"
    Then the response status code should be 404

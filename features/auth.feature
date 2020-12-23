Feature: Login/logout
  In order to use the API
  As a user
  I must be able to login and logout

  Scenario: As unknown user, I cannot log in
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "invalid",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    And the JSON should be equal to:
    """
    {
      "code": 401,
      "message": "Invalid credentials."
    }
    """

  Scenario Outline: As a valid user, I can log in with my username
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "<username>",
      "password": "password"
    }
    """
    Then the response status code should be 204
    And a refresh-token has been created on user "<username>"
    When I send a "GET" request to "/api/members/<username>"
    Then the response status code should be 200
    Examples:
      | username |
      | member-2 |
      | member-3 |
      | member-4 |
      | member-5 |

  Scenario Outline: As an inactive or unverified user, I cannot log in
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "<username>",
      "password": "password"
    }
    """
    Then the response status code should be 401
    Examples:
      | username         |
      | member-banned    |
      | member-taken-out |

# todo Cookie remove seems buggy on Mink & BrowserKit
#  @wip
#  Scenario: As an authenticated user, I can log out
#    Given I am authenticated as "member-2"
#    When I send a "GET" request to "/api/logout"
#    Then the response status code should be 302
#    When I send a "GET" request to "/api/members"
#    Then the response status code should be 401

  Scenario: As anonymous, I cannot login after 3 failed attempts on the same account
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-2",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-2",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-2",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-2",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    And the response should be in JSON

  Scenario: As anonymous, I am not blocked after 3 failed attempts on different accounts
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-2",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-3",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-4",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    Given I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/login" with body:
    """
    {
      "username": "member-5",
      "password": "invalid"
    }
    """
    Then the response status code should be 401
    And the response should be in JSON
    And the JSON node "hydra:description" should not exist

# todo Add a test to check auth with refresh-token

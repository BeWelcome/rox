Feature:
  In order to manage my subtrips,
  As a member,
  I should be able to list, get, create, update and delete my subtrips

  # POST
  Scenario: As anonymous, I cannot create a subtrip
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/api/subtrips" with body:
    """
    {
      "location": "/api/locations/2921044",
      "arrival": "2021-02-19",
      "departure": "2021-02-21",
      "options": ["MeetLocals", "LookingForHosts"],
      "trip": "/api/trips/1"
    }
    """
    Then the response status code should be 401

  Scenario: As anonymous, I can create a subtrip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/api/subtrips" with body:
    """
    {
      "location": "/api/locations/2921044",
      "arrival": "2021-02-19",
      "departure": "2021-02-21",
      "options": ["MeetLocals", "LookingForHosts"],
      "trip": "/api/trips/1"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/subtrip/item/schema.json"

  Scenario: As anonymous, I cannot create a subtrip on another member's trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/api/subtrips" with body:
    """
    {
      "location": "/api/locations/2921044",
      "arrival": "2021-02-19",
      "departure": "2021-02-21",
      "options": ["MeetLocals", "LookingForHosts"],
      "trip": "/api/trips/2"
    }
    """
    Then the response status code should be 422
    And the response should be in JSON
    And the JSON node "hydra:description" should be equal to "trip: This value is not valid."

  # GET
  Scenario: As anonymous, I cannot get a subtrip
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/subtrips/1"
    Then the response status code should be 401

  Scenario Outline: As a member, I can get any member's subtrip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "<uri>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/subtrip/item/schema.json"
    Examples:
      | uri             |
      | /api/subtrips/1 |
      | /api/subtrips/2 |
      | /api/subtrips/3 |

  # PUT
  Scenario: As anonymous, I cannot update a subtrip
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/subtrips/1" with body:
    """
    {
      "location": "/api/locations/2921044",
      "arrival": "2021-02-19",
      "departure": "2021-02-21",
      "options": ["MeetLocals", "LookingForHosts"]
    }
    """
    Then the response status code should be 401

  Scenario: As anonymous, I can update my trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/subtrips/1" with body:
    """
    {
      "location": "/api/locations/2921044",
      "arrival": "2021-02-19",
      "departure": "2021-02-21",
      "options": ["MeetLocals", "LookingForHosts"]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/subtrip/item/schema.json"

  Scenario: As anonymous, I cannot update a subtrip from another member's trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/subtrips/3" with body:
    """
    {
      "location": "/api/locations/2921044",
      "arrival": "2021-02-19",
      "departure": "2021-02-21",
      "options": ["MeetLocals", "LookingForHosts"]
    }
    """
    Then the response status code should be 403

  # DELETE
  Scenario: As anonymous, I cannot delete a subtrip
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/subtrips/1"
    Then the response status code should be 401

  Scenario: As anonymous, I can update my trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/subtrips/1"
    Then the response status code should be 204
    And the response should be empty

  Scenario: As anonymous, I cannot update another member's trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/subtrips/3"
    Then the response status code should be 403

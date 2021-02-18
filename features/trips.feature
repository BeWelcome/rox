Feature:
  In order to manage my trips,
  As a member,
  I should be able to list, get, create, update and delete my trips

  # List
  Scenario: As anonymous, I cannot list trips
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/members/member-2/trips"
    Then the response status code should be 401

  Scenario Outline: As a member, I can list any member trips
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "<uri>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/trip/collection/schema.json"
    Examples:
      | uri                         |
      | /api/members/member-2/trips |
      | /api/members/member-3/trips |

  # POST
  Scenario: As anonymous, I cannot create a trip
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/api/trips" with body:
    """
    {
      "summary": "Lorem ipsum dolor sit amet",
      "description": "Lorem ipsum dolor sit amet",
      "countOfTravellers": 3,
      "additionalInfo": "single",
      "subtrips": [
        {
          "location": "/api/locations/2921044",
          "arrival": "2021-02-19",
          "departure": "2021-02-21",
          "options": ["MeetLocals", "LookingForHosts"]
        }
      ]
    }
    """
    Then the response status code should be 401

  Scenario: As anonymous, I cannot create a trip without a subtrip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/api/trips" with body:
    """
    {
      "summary": "Lorem ipsum dolor sit amet",
      "description": "Lorem ipsum dolor sit amet",
      "countOfTravellers": 3,
      "additionalInfo": "single"
    }
    """
    Then the response status code should be 422
    And the response should be in JSON
    And the JSON node "hydra:description" should be equal to "subtrips: This collection should contain 1 element or more."

  Scenario: As anonymous, I can create a trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/api/trips" with body:
    """
    {
      "summary": "Lorem ipsum dolor sit amet",
      "description": "Lorem ipsum dolor sit amet",
      "countOfTravellers": 3,
      "additionalInfo": "single",
      "subtrips": [
        {
          "location": "/api/locations/2921044",
          "arrival": "2021-02-19",
          "departure": "2021-02-21",
          "options": ["MeetLocals", "LookingForHosts"]
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/trip/item/schema.json"

  # GET
  Scenario: As anonymous, I cannot get a trip
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/trips/1"
    Then the response status code should be 401

  Scenario Outline: As a member, I can get any member's trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "<uri>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/trip/item/schema.json"
    Examples:
      | uri          |
      | /api/trips/1 |
      | /api/trips/2 |

  # PUT
  Scenario: As anonymous, I cannot update a trip
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/trips/1" with body:
    """
    {
      "summary": "Lorem ipsum dolor sit amet",
      "description": "Lorem ipsum dolor sit amet",
      "countOfTravellers": 3
    }
    """
    Then the response status code should be 401

  Scenario: As anonymous, I can update my trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/trips/1" with body:
    """
    {
      "summary": "Lorem ipsum dolor sit amet",
      "description": "Lorem ipsum dolor sit amet",
      "countOfTravellers": 3
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "tests/Behat/json/trip/item/schema.json"

  Scenario: As anonymous, I cannot update another member's trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/trips/2" with body:
    """
    {
      "summary": "Lorem ipsum dolor sit amet",
      "description": "Lorem ipsum dolor sit amet",
      "countOfTravellers": 3
    }
    """
    Then the response status code should be 403

  # DELETE
  Scenario: As anonymous, I cannot delete a trip
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/trips/1"
    Then the response status code should be 401

  Scenario: As anonymous, I can update my trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/trips/1"
    Then the response status code should be 204
    And the response should be empty

  Scenario: As anonymous, I cannot update another member's trip
    Given I am authenticated as "member-2"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/trips/2"
    Then the response status code should be 403

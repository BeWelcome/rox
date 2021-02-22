Feature:
  In order to manage my subtrips,
  As a member,
  I should be able to list, get, create, update and delete my subtrips

  # POST
  Scenario: As anonymous, I cannot create a subtrip
    When I create a subtrip for Berlin in 2 days for 2 days
    Then I should be unauthorized

  Scenario: As an authenticated user, I can create a subtrip
    Given I am authenticated as "member-2"
    When I create a subtrip for Berlin in 2 days for 2 days
    Then the subtrip should have been successfully created

  Scenario: As an authenticated user, I cannot add a subtrip to another member's trip
    Given I am authenticated as "member-2"
    When I create a subtrip for Berlin on another member's trip
    Then I should see the following errors:
      | trip: This value is not valid. |

  # GET
  Scenario: As anonymous, I cannot get a subtrip
    When I get a subtrip for Berlin
    Then I should be unauthorized

  Scenario Outline: As a member, I can get any member's subtrip
    Given I am authenticated as "member-2"
    When I get a subtrip for "<city>"
    Then I should see the subtrip
    Examples:
      | city     |
      | Berlin   |
      | Jayapura |
      | Munich   |

  # PUT
  Scenario: As anonymous, I cannot update a subtrip
    When I update my subtrip for Berlin
    Then I should be unauthorized

  Scenario: As an authenticated user, I can update my trip
    Given I am authenticated as "member-2"
    When I update my subtrip for Berlin
    Then I should see the subtrip

  Scenario: As an authenticated user, I cannot update a subtrip from another member's trip
    Given I am authenticated as "member-2"
    When I update the subtrip for Munich of another member's trip
    Then I should be forbidden

  # DELETE
  Scenario: As anonymous, I cannot delete a subtrip
    When I delete my subtrip for Berlin
    Then I should be unauthorized

  Scenario: As an authenticated user, I can delete any subtrip that I own
    Given I am authenticated as "member-2"
    When I delete my subtrip for Berlin
    Then the subtrip should have been successfully deleted

  Scenario: As an authenticated user, I cannot delete another member's trip
    Given I am authenticated as "member-2"
    When I delete the subtrip for Munich of another member's trip
    Then I should be forbidden

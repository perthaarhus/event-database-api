Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Create Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The 1700s",
      "occurrences": [ { "startDate": "1700-01-01", "endDate": "1799-12-31" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/1"

    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The 1800s",
      "occurrences": [ { "startDate": "1800-01-01", "endDate": "1899-12-31" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The 1900s",
      "occurrences": [ { "startDate": "1900-01-01", "endDate": "1999-12-31" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/3"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The 2000s",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2099-12-31" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/4"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The 2100s",
      "occurrences": [ { "startDate": "2100-01-01", "endDate": "2199-12-31" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/5"

  Scenario: Get events
    When I send a "GET" request to "/api/events?occurrences.startDate[after]=1600-01-01"
    And the JSON node "hydra:member" should have 5 elements

  Scenario: Get events
    When I send a "GET" request to "/api/events?occurrences.endDate[after]=1700-12-01&occurrences.startDate[before]=1800-02-01&order[name]=desc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].name" should be equal to "The 1700s"
    And the JSON node "hydra:member[1].name" should be equal to "The 1800s"

  # Scenario: Get past events
  #   When I send a "GET" request to "/api/events?occurrences.startDate[before]=2050-01-01"
  #   And the JSON node "hydra:member" should have 1 element
  #   And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

  # Scenario: Get all events
  #   When I send a "GET" request to "/api/events?occurrences.startDate[after]=1900-01-01"
  #   And the JSON node "hydra:member" should have 2 elements
  #   And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
  #   And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

  @dropSchema
  Scenario: Drop schema

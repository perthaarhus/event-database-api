Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Count Events
    When I sign in with username "api-read" and password "apipass"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Create an event with multiple occurrences
    When I authenticate as "api-write"
    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Repeating event",
      "occurrences": [ {
        "startDate": "2001-01-01",
        "endDate": "2002-01-01"
      },
      {
        "startDate": "2002-01-01",
        "endDate": "2003-01-01"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/1",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [
        {
          "@id": "\/api\/occurrences\/1",
          "@type": "Occurrence",
          "event": "\/api\/events\/1",
          "startDate": "2001-01-01T00:00:00+00:00",
          "endDate": "2002-01-01T00:00:00+00:00",
          "venue": null
        },
        {
          "@id": "\/api\/occurrences\/2",
          "@type": "Occurrence",
          "event": "\/api\/events\/1",
          "startDate": "2002-01-01T00:00:00+00:00",
          "endDate": "2003-01-01T00:00:00+00:00",
          "venue": null
        }
      ],
      "description": null,
      "image": null,
      "name": "Repeating event",
      "url": null
    }
    """

  Scenario: Create another event with multiple occurrences
    When I authenticate as "api-write"
    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another repeating event",
      "occurrences": [ {
        "startDate": "2003-01-01",
        "endDate": "2004-01-01"
      },
      {
        "startDate": "2004-01-01",
        "endDate": "2005-01-01"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/2",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [
        {
          "@id": "\/api\/occurrences\/3",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2003-01-01T00:00:00+00:00",
          "endDate": "2004-01-01T00:00:00+00:00",
          "venue": null
        },
        {
          "@id": "\/api\/occurrences\/4",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2004-01-01T00:00:00+00:00",
          "endDate": "2005-01-01T00:00:00+00:00",
          "venue": null
        }
      ],
      "description": null,
      "image": null,
      "name": "Another repeating event",
      "url": null
    }
    """

  Scenario: Count Events
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 4 elements

  Scenario: Can filter on occurrence start date (before)
    When I authenticate as "api-read"
    When I send a "GET" request to "/api/events?occurrences.startDate[before]=2002-01-01"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 1 elements

  # Scenario: Can filter on occurrence start date (exact)
  #   When I authenticate as "api-read"
  #   When I send a "GET" request to "/api/events?occurrences.startDate=2002-01-01"
  #   Then the response status code should be 200
  #   And the response should be in JSON
  #   And the header "Content-Type" should be equal to "application/ld+json"
  #   And the JSON node "hydra:member" should have 2 elements

  Scenario: Can filter on occurrence start date (after)
    When I authenticate as "api-read"
    When I send a "GET" request to "/api/events?occurrences.startDate[after]=2002-01-01"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 2 elements

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"

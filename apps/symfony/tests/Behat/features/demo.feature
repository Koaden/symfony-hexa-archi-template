Feature: Documentation page

  Scenario: Viewing the documentation page
    Given I am on "/"
    Then I should see "Welcome to Symfony"
    And I should see "Repository"
    And I should see "HTMX test"

  Scenario: Loading the HTMX demo without JavaScript
    Given I am on "/"
    When I follow "Test HTMX"
    Then I should be on "/demo/now"
    And I should see "Demo loaded at"

  Scenario: Viewing the demo fragment
    Given I am on "/demo/now"
    Then I should see "Demo loaded at"

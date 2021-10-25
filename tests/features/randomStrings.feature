Feature: Any helper for strings
  In order to generate random strings
  As a developer of PHP tools
  I need one helper function

  @randomString
  Scenario: Generate random strings
    Given I generate a random string of length 16
    When I generate another random string of same length
    Then both strings have to differ
    And both strings have to be of given length

  @randomString
  Scenario: Generate random string from single alphabet
    Given the alphabets
      | alphabet   |
      | abcdef0123 |
    When I generate a random string of length 16
    Then there is only one alphabet given
    And the generated string matches each RegEx
      | RegEx              |
      | /^([a-f0-3]){16}$/ |

  @randomString
  Scenario: Generate random string with multiple alphabets
    Given the alphabets
      | alphabet |
      | abcdef   |
      | 0123     |
    When I generate a random string of length 16
    Then the generated string matches each RegEx
      | RegEx              |
      | /.*[a-f].*/        |
      | /.*[0-3].*/        |
      | /^([a-f0-3]){16}$/ |

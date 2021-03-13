Feature: Get RegEx occurences
  In order to work with Regexes on large templates
  As a developer of PHP tools
  I need a simplified way to get special parameters of all regex matches

  @getRegexOccurences
  Scenario: Twig – any variable with default value
    # Twig variable extract
    Given the input string
      """
      Lorem ipsum {{ dolor | default( "sit amet" ) }}
      """
    And the occurences RegEx "/\{\{\s*.*?\s*\|\s*default\s*\((.*?)\)\s*\}\}/mi"
    And the additional occurences group "default" with commaseparated groups "1"
    When running the "getRegexOccurences" function
    Then I should get 1 match
    And "default" matches "sit amet" for variable 0

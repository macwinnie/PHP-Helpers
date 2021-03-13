Feature: Format to RegEx
  In order to check strings against a Format-String
  As a developer of PHP tools
  I need to be able to rewrite a Format-String into a Regular Expression

  @format2regex
  Scenario: Simple template variable extract
    # LDAP User DN with missing user and company
    Given the input string
      """
      uid=%s,ou=people,dc=%s,dc=com
      """
    When running the "format2regex" function
    And fetching values by generated RegEx from "uid=jdoe,ou=people,dc=example,dc=com"
    Then I should get 2 values
    And "jdoe" is matched value 1
    And "example" is matched value 2

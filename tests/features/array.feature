Feature: work with arrays
  In order to work with complex data structures
  As a developer of PHP tools
  I need to be able to work with arrays

  @getArrayValue
  Scenario: Basic array test
    Given the JSON array
      """
      {
        "lorem": "ipsum",
        "dolor": "sit"
      }
      """
    When I search for the key-tree "dolor"
    Then I should get the value "sit"

  @getArrayValue
  Scenario: Basic array test with default value
    Given the JSON array
      """
      {
        "lorem": "ipsum",
        "dolor": "sit"
      }
      """
    When I search for the key-tree "amet" with default "consectetur"
    Then I should get the value "consectetur"

  @getArrayValue
  Scenario: Nested array test
    Given the JSON array
      """
      {
        "lorem": {
          "ipsum": "dolor",
          "sit" : "amet"
        }
      }
      """
    When I search for the key-tree "lorem.ipsum"
    Then I should get the value "dolor"

  @getArrayValue
  Scenario: Nested array with dotted key test
    Given the JSON array
      """
      {
        "lorem.ipsum": {
          "dolor": "sit",
          "amet" : "consectetur"
        }
      }
      """
    When I search for the key-tree "lorem.ipsum.dolor"
    Then I should get the value "sit"

  @getArrayValue
  Scenario: Nested array with dotted key and removal test
    Given the JSON array
      """
      {
        "lorem.ipsum": {
          "dolor": "sit",
          "amet" : "consectetur"
        }
      }
      """
    When I extract – search and remove – the key-tree "lorem.ipsum.dolor"
    Then I should get the value "sit"
    And the JSON representation of the remaining array should look like
      """
      {
        "lorem.ipsum": {
          "amet" : "consectetur"
        }
      }
      """

  @inArrayRecursive @cur
  Scenario: Successfully find an element within an array
    Given the JSON array
      """
      {
        "lorem.ipsum": {
          "dolor": {
            "sit": "amet",
            "consectetur" : "sadipscing"
          }
        }
      }
      """
    Then searching for JSON returns "true"
      """
      "sadipscing"
      """

  @inArrayRecursive @cur
  Scenario: Successfully find an element within an array
    Given the JSON array
      """
      {
        "lorem.ipsum": {
          "dolor": {
            "sit": "amet",
            "consectetur" : "sadipscing"
          }
        }
      }
      """
    Then searching for JSON returns "true"
      """
      {
        "sit": "amet",
        "consectetur" : "sadipscing"
      }
      """

  @inArrayRecursive @cur
  Scenario: Don't find an element within an array
    Given the JSON array
      """
      {
        "lorem.ipsum": {
          "dolor": {
            "sit": "amet",
            "consectetur" : "sadipscing"
          }
        }
      }
      """
    Then searching for JSON returns "false"
      """
      "lorem"
      """

Feature: get array value
  In order to get a value out of an array
  As a developer of PHP tools
  I need to be able to define a dot-joined key-tree to fetch a specific value

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

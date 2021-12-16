Feature: Any feature
  In order to keep the features maintainable
  As the provider of this PHP Helper
  I need to have one feature for tests with only one scenario

  @recursiveDeleteDirectory
  Scenario: Delete a directory with content recursively
    Given the existing empty directory "tmp"
    And the existing new directory "tmp/lorem/ipsum"
    And the existing new empty file "tmp/lorem/ipsum/dolor"
    Then by removing "tmp" the directory does no more exist

  @val2boolEmptyNull
  Scenario: Test value to bool / empty / null value
    Given the value matrix
      | string  | expected |
      | lorem   | lorem    |
      | 1       | 1        |
      | (null)  | NULL     |
      | null    | NULL     |
      | empty   |          |
      | (empty) |          |
      | (true)  | true     |
      | true    | true     |
      | (yes)   | true     |
      | yes     | true     |
      | (false) | false    |
      | false   | false    |
      | (no)    | false    |
      | no      | false    |
    Then boolean function "val2boolEmptyNull" returns expected values

  @env
  Scenario: Get value out of quoted value
    Given the value matrix
      | env   | val     | expected |
      | TEST1 | "lorem" | lorem    |
      | TEST2 | 'lorem' | lorem    |
      | TEST3 | 'lorem" | 'lorem"  |
      | TEST4 | false   | false    |
      | TEST5 | yes     | true     |
      | TEST6 | NULL    | null     |
      | TEST7 | EMPTY   |          |
    Then setting env and receiving the value matches the expected

  @trim
  Scenario: Get trimmed string out of value
    Given the value matrix
      | string    | expected |
      | "  lorem" | lorem  |
      | "lorem  " | lorem  |
      | " lorem " | lorem  |
    And trimming quotes from "string" attributes
    Then function "trimIfString" returns expected values

  @strToBool
  Scenario: Test value to boolean
    Given the value matrix
      | string  | expected |
      | lorem   | false    |
      | 1       | true     |
      | 0       | false    |
      | (null)  | false    |
      | null    | false    |
      | empty   | false    |
      | (empty) | false    |
      | (true)  | true     |
      | true    | true     |
      | (yes)   | true     |
      | yes     | true     |
      | (false) | false    |
      | false   | false    |
      | (no)    | false    |
      | no      | false    |
    Then boolean function "strToBool" returns expected values

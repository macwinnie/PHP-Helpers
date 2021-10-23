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
    Given the string - expected set
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
    Then transforming with val2boolEmptyNull returns the expected

  @env
  Scenario: Get value out of quoted value
    Given the env - value - expected set
      | env   | val     | expected |
      | TEST1 | "lorem" | lorem    |
      | TEST2 | 'lorem' | lorem    |
      | TEST3 | 'lorem" | 'lorem"  |
      | TEST4 | false   | false    |
      | TEST5 | yes     | true     |
      | TEST6 | NULL    | null     |
      | TEST7 | EMPTY   |          |
    Then setting env and receiving the value matches the expected

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

  @trim
  Scenario: Get trimmed string out of value
    Given the string - expected set
      | string    | expected |
      | "  lorem" | lorem  |
      | "lorem  " | lorem  |
      | " lorem " | lorem  |
    And trimming quotes from "string" attributes
    Then trimming if is string returns expected values

  @camelize
  Scenario: Get camelized string
    Given the string - expected set
      | string            | expected          |
      | lorem Ipsum DOLOR | loremIpsumDolor   |
      | lorem IpsumDolor  | loremIpsumdolor   |
      | lorem_ipsum_dolor | lorem_ipsum_dolor |
      | Erd Äpfel         | erdAepfel         |
    Then camelize returns expected values

  @camelize
  Scenario: Get camelized string out of snake
    Given the string - expected set
      | string            | expected         |
      | lorem_ipsum_dolor | loremIpsumDolor  |
      | lorem_ipsumDolor  | loremIpsumdolor  |
      | lorem_ipsum Dolor | loremIpsum dolor |
    Then camelize by underscore returns expected values

  @camelize
  Scenario: Get camelized string keeping existing camels
    Given the string - expected set
      | string           | expected        |
      | Lorem IpsumDolor | loremIpsumDolor |
      | LoremIpsum_dolor | loremIpsumDolor |
    Then camelize keeping camels returns expected values

  @camelize
  Scenario: invert camelization of a string
    Given the string - expected set
      | string              | expected               |
      | pocForCamelCase     | poc For Camel Case     |
      | PascalCaseAlsoWorks | Pascal Case Also Works |
    Then decamelize returns expected values

  @pascalize
  Scenario: Get PascalCase string (single test since small difference between camelCase and PascalCase)
    Given the string - expected set
      | string            | expected          |
      | lorem Ipsum DOLOR | LoremIpsumDolor   |
      | lorem IpsumDolor  | LoremIpsumdolor   |
      | lorem_ipsum_dolor | Lorem_ipsum_dolor |
      | Erd Äpfel         | ErdAepfel         |
    Then pascalize returns expected values

  @snakify
  Scenario: Get snakified string
    Given the string - expected set
      | string            | expected          |
      | lorem Ipsum DOLOR | lorem_ipsum_dolor |
      | lorem IpsumDolor  | lorem_ipsumdolor   |
      | lorem_ipsum_dolor | lorem_ipsum_dolor |
      | Erd Äpfel         | erd_aepfel         |
    Then snakify returns expected values

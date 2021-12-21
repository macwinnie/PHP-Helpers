Feature: Case feature
  In order to convert strings into special case formats
  As a developer of PHP software
  I need to have some functions realizing that

  @camelize
  Scenario: Get camelized string
    Given the value matrix
      | string            | expected          |
      | lorem Ipsum DOLOR | loremIpsumDolor   |
      | lorem IpsumDolor  | loremIpsumdolor   |
      | lorem_ipsum_dolor | lorem_ipsum_dolor |
    Then function "camelize" returns expected values

  @camelize @skip
  Scenario: Get camelized string
    Given the value matrix
      | string    | expected   |
      | Erd Äpfel | erdAepfel  |
    Then function "camelize" returns expected values

  @camelize
  Scenario: Get camelized string out of snake
    Given the value matrix
      | string            | expected         |
      | lorem_ipsum_dolor | loremIpsumDolor  |
      | lorem_ipsumDolor  | loremIpsumdolor  |
      | lorem_ipsum Dolor | loremIpsum dolor |
    Then camelize by underscore returns expected values

  @camelize
  Scenario: Get camelized string keeping existing camels
    Given the value matrix
      | string           | expected        |
      | Lorem IpsumDolor | loremIpsumDolor |
      | LoremIpsum_dolor | loremIpsumDolor |
    Then camelize keeping camels returns expected values

  @camelize
  Scenario: invert camelization of a string
    Given the value matrix
      | string              | expected               |
      | pocForCamelCase     | poc For Camel Case     |
      | PascalCaseAlsoWorks | Pascal Case Also Works |
    Then function "decamelize" returns expected values

  @pascalize
  Scenario: Get PascalCase string (single test since small difference between camelCase and PascalCase)
    Given the value matrix
      | string            | expected          |
      | lorem Ipsum DOLOR | LoremIpsumDolor   |
      | lorem IpsumDolor  | LoremIpsumdolor   |
      | lorem_ipsum_dolor | Lorem_ipsum_dolor |
    Then function "pascalize" returns expected values

  @pascalize @skip
  Scenario: Get PascalCase string (single test since small difference between camelCase and PascalCase)
    Given the value matrix
      | string    | expected   |
      | Erd Äpfel | erd_aepfel |
    Then function "pascalize" returns expected values

  @snakify
  Scenario: Get snakified string
    Given the value matrix
      | string            | expected          |
      | lorem Ipsum DOLOR | lorem_ipsum_dolor |
      | lorem IpsumDolor  | lorem_ipsumdolor  |
      | lorem_ipsum_dolor | lorem_ipsum_dolor |
    Then function "snakify" returns expected values

  @snakify @skip
  Scenario: Get snakified string
    Given the value matrix
      | string    | expected   |
      | Erd Äpfel | erd_aepfel |
    Then function "snakify" returns expected values

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

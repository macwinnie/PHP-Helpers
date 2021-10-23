Feature: Test the logger
  In order to log messages, that occur while executing code
  As a developer of PHP tools
  I need a helper to manage those logs

  @logger
  Scenario: Generating the log message
    Given I log the message "lorem ipsum" with level "return"
    Then the message matches the regex "/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}.[0-9]{6} \[RETURN\] \[[^\]]+?\] \[[0-9]+\]: lorem ipsum/"
    And the message contains current class name

  @logger
  Scenario: Generating the log message without microseconds
    Given the env variable "LOG_MICROSEC" with value "false"
    And I log the message "lorem ipsum" with level "return"
    Then the message matches the regex "/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \[RETURN\] \[[^\]]+?\] \[[0-9]+\]: lorem ipsum/"
    And the message contains current class name

  @logger
  Scenario: Writing a log message
    Given the logging path "tmp/logging"
    And the env variable "LOGLEVEL" with value "debug"
    When I log the message "lorem ipsum" with level "error"
    Then the global logfile should contain an entry with that message
    And the loglevel logfile should contain an entry with that message

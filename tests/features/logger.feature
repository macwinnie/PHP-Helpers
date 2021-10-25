Feature: Test the logger
  In order to log messages, that occur while executing code
  As a developer of PHP tools
  I need a helper to manage those logs

  @logger
  Scenario: Generating the log message
    Given I log the message "lorem ipsum" with level "debug"
    Then the message matches the regex "/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}.[0-9]{6} \[DEBUG\] \[[^\]]+?\] \[[0-9]+\]: lorem ipsum/"
    And the message contains current class name

  @logger
  Scenario: Generating the log message without microseconds
    Given the env variable "LOG_MICROSEC" with value "false"
    And I log the message "lorem ipsum" with level "error"
    Then the message matches the regex "/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} \[ERROR\] \[[^\]]+?\] \[[0-9]+\]: lorem ipsum/"
    And the message contains current class name

  @logger
  Scenario: Logging with lower level not respected
    Given the logging path "tmp/logging"
    And the env variable "LOG_LEVEL" with value "error"
    And the env variable "LOG_COMBINED" with value "true"
    And the env variable "LOG_ONLY_COMBINED" with value "false"
    When I log the message "lorem ipsum" with level "debug"
    Then the global logfile does not exist
    And the loglevel logfile does not exist

  @logger
  Scenario: Writing a log message to loglevel file and global logfile
    Given the logging path "tmp/logging"
    And the env variable "LOG_LEVEL" with value "debug"
    And the env variable "LOG_COMBINED" with value "true"
    And the env variable "LOG_ONLY_COMBINED" with value "false"
    When I log the message "lorem ipsum" with level "error"
    Then the global logfile should contain an entry with that message
    And the loglevel logfile should contain an entry with that message

  @logger
  Scenario: Writing a log message to global logfile only
    Given the logging path "tmp/logging"
    And the env variable "LOG_LEVEL" with value "debug"
    And the env variable "LOG_COMBINED" with value "true"
    And the env variable "LOG_ONLY_COMBINED" with value "true"
    When I log the message "lorem ipsum" with level "error"
    Then the global logfile should contain an entry with that message
    And the loglevel logfile does not exist

  @logger
  Scenario: Writing a log message to loglevel file only
    Given the logging path "tmp/logging"
    And the env variable "LOG_LEVEL" with value "debug"
    And the env variable "LOG_COMBINED" with value "false"
    When I log the message "lorem ipsum" with level "error"
    Then the global logfile does not exist
    And the loglevel logfile should contain an entry with that message

Feature: Support of template option and configuration file

  Background:
    Given Directory structure for templates
    And Application with current path "project"
    And a path containing a composer.json with a "psr-4" namespace
    And option for disabling parent exception search is "set"

  Scenario: Template option was passed
    Given Template path is passed as option
    When the application is executed
    Then templates from template path should have been used

  Scenario: Template is used from configuration file as the patch matches
    Given Project template path configured in config
    But Template path is not passed as option
    When the application is executed
    Then template from project configuration from global configuration should have been used

  Scenario: Global Template is used from configuration file
    Given Global template path configured in config
    But Template path is not passed as option
    When the application is executed
    Then template from global configuration from global configuration should have been used

  Scenario: exceptions are generated by the default templates
    Given Template path is passed as option
    But interface template is remove from passed template path
    When the application is executed
    Then templates from template path should have been used
    But template from passed path for interface shouldn't have been used

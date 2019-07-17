#-------------------------------------------------------
# Copyright (C) 2019 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

Feature: User-Interface
In order to use REDCap-ETL
As a non-admin user
I need to be able to create, copy, rename and delete configurations
  and get help for a REDCap-ETL enabled project

  Background:
    Given I am on "/"
    And I am logged in as user
    And I follow "My Projects"
    When I select the test project
    And I follow "REDCap-ETL"

  Scenario: Delete existing schedule configuration (if any)
    When I follow "ETL Configurations"
    And I delete configuration "bh-sched" if it exists
    Then I should not see "bh-sched"
    And I should not see "Error:"

  Scenario: Create configuration
    When I fill in "configurationName" with "bh-sched"
    And I press "Add"
    Then I should see "bh-sched"

  Scenario: Configure configuration
    When I follow configuration "bh-sched"
    And I configure configuration "behat"
    And I fill in "Table name prefix" with "sched_"
    And I check "email_errors"
    And I check "email_summary"
    And I press "Save"
    Then I should see "Extract Settings"
    And I should see "Table"
    And the "Table name prefix" field should contain "sched_"

  Scenario: Schedule configuration
    When I follow "Schedule"
    And I select "bh-sched" from "configName"
    And I select "(embedded server)" from "server"
    And I schedule for next hour
    And I press "Save"
    And I wait for 10 seconds
    Then I should see "Configuration:"
    And I should see "Server:"
    And I should see "(embedded server)"
    #And I select "behat" from "configName"
    #And I press "Run"
    #Then I should see "Configuration:"
    #And I should see "Created table"
    #And I should see "Number of record_ids found: 100"
    #And I should see "Processing complete."
    #But I should not see "Error:"
    

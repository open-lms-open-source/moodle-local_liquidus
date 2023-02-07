@local @local_liquidus
Feature: Liquidus plugin configuration
  In order to configure the Liquidus plugin,
  As an admin I need to be able to access its settings

  @javascript
  Scenario: Access advanced configuration of plugin
    Given the following config values are set as admin:
      | local_liquidus_disable_tracker_config | 0 |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Liquidus" in site administration
    And I should see "Enabled"
    And I should see "Handle masquerading"
    And I should see "Tracking Admins"
    And I should see "Tracking Non-Admins"
    And I should see "Clean URLs"
    Then I should see "Share identifiable data of user"

  @javascript
  Scenario: No access advanced configuration of plugin
    Given the following config values are set as admin:
      | local_liquidus_disable_tracker_config | 1 |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Liquidus" in site administration
    And I should see "Enabled"
    And I should not see "Handle masquerading"
    And I should not see "Tracking Admins"
    And I should not see "Tracking Non-Admins"
    And I should not see "Clean URLs"
    Then I should not see "Share identifiable data of user"
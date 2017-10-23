<?php

namespace Drupal\domain_access\Tests;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Url;
use Drupal\domain\Tests\DomainTestBase;
use Drupal\node\Entity\NodeType;

/**
 * Tests the domain access entity reference field type.
 *
 * @group domain_access
 */
class DomainAccessFieldTest extends DomainTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('domain', 'domain_access', 'field', 'field_ui', 'user');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create 5 domains.
    $this->domainCreateTestDomains(5);
  }

  /**
   * Tests that the fields are accessed properly.
   */
  public function testDomainAccessFields() {
    $label = 'Send to all affiliates';

    // Test a user who can access all domain settings.
    $user1 = $this->drupalCreateUser(array('create article content', 'publish to any domain'));
    $this->drupalLogin($user1);

    // Visit the article creation page.
    $this->drupalGet('node/add/article');
    $this->assertResponse(200, 'Article creation found.');

    // Check for the form options.
    $domains = \Drupal::service('domain.storage')->loadMultiple();
    foreach ($domains as $domain) {
      $this->assertText($domain->label(), 'Domain form item found.');
    }
    $this->assertText($label, 'All affiliates field found.');

    // Test a user who can access some domain settings.
    $user2 = $this->drupalCreateUser(array('create article content', 'publish to any assigned domain'));
    $active_domain = array_rand($domains, 1);
    $this->addDomainToEntity('user', $user2->id(), $active_domain);
    $this->drupalLogin($user2);

    // Visit the article creation page.
    $this->drupalGet('node/add/article');
    $this->assertResponse(200, 'Article creation found.');

    // Check for the form options.
    foreach ($domains as $domain) {
      if ($domain->id() == $active_domain) {
        $this->assertRaw('>' . $domain->label() . '</label>', 'Domain form item found.');
      }
      else {
        $this->assertNoRaw('>' . $domain->label() . '</label>', 'Domain form item not found.');
      }
    }
    $this->assertNoText($label, 'All affiliates field not found.');

    // Test a user who can access no domain settings.
    $user3 = $this->drupalCreateUser(array('create article content'));
    $this->drupalLogin($user3);

    // Visit the article creation page.
    $this->drupalGet('node/add/article');
    $this->assertResponse(200, 'Article creation found.');

    // Check for the form options.
    foreach ($domains as $domain) {
      $this->assertNoText($domain->label(), 'Domain form item not found.');
    }
    $this->assertNoText($label, 'All affiliates field not found.');

    // Attempt saving the node.
    // The domain/domain affiliates fields are not accessible to this user.
    // The save will fail with an EntityStorageException until
    // https://www.drupal.org/node/2609252 is fixed.
    $edit = array();
    $edit['title[0][value]'] = $this->randomMachineName(8);
    $edit['body[0][value]'] = $this->randomMachineName(16);
    $this->drupalPostForm('node/add/article', $edit, t('Save'));

    // Check that the node exists in the database.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $this->assertTrue($node, 'Node found in database.');

    // Test a user who can assign users to domains.
    $user4 = $this->drupalCreateUser(array('administer users', 'assign editors to any domain'));
    $this->drupalLogin($user4);

    // Visit the account creation page.
    $this->drupalGet('admin/people/create');
    $this->assertResponse(200, 'User creation found.');

    // Check for the form options.
    foreach ($domains as $domain) {
      $this->assertText($domain->label(), 'Domain form item found.');
    }

    // Test a user who can assign users to some domains.
    $user5 = $this->drupalCreateUser(array('administer users', 'assign domain editors'));
    $active_domain = array_rand($domains, 1);
    $this->addDomainToEntity('user', $user5->id(), $active_domain);
    $this->drupalLogin($user5);

    // Visit the account creation page.
    $this->drupalGet('admin/people/create');
    $this->assertResponse(200, 'User creation found.');

    // Check for the form options.
    foreach ($domains as $domain) {
      if ($domain->id() == $active_domain) {
        $this->assertRaw('>' . $domain->label() . '</label>', 'Domain form item found.');
      }
      else {
        $this->assertNoRaw('>' . $domain->label() . '</label>', 'Domain form item not found.');
      }
    }

    // Test a user who can access no domain settings.
    $user6 = $this->drupalCreateUser(array('administer users'));
    $this->drupalLogin($user6);

    // Visit the account creation page.
    $this->drupalGet('admin/people/create');
    $this->assertResponse(200, 'User creation found.');

    // Check for the form options.
    foreach ($domains as $domain) {
      $this->assertNoText($domain->label(), 'Domain form item not found.');
    }

    // Test a user who can access all domain settings.
    $user7 = $this->drupalCreateUser(array('bypass node access', 'publish to any domain'));
    $this->drupalLogin($user7);

    // Create a new content type and test that the fields are created.
    // Create a content type programmatically.
    $type = $this->drupalCreateContentType();

    $type_exists = (bool) NodeType::load($type->id());
    $this->assertTrue($type_exists, 'The new content type has been created in the database.');

    // Visit the article creation page.
    $this->drupalGet('node/add/' . $type->id());
    $this->assertResponse(200, $type->id() . ' creation found.');

    // Check for the form options.
    $domains = \Drupal::service('domain.storage')->loadMultiple();
    foreach ($domains as $domain) {
      $this->assertText($domain->label(), 'Domain form item found.');
    }
    $this->assertText($label, 'All affiliates field found.');

    // Test user without access to affiliates field editing their user page.
    $user8 = $this->drupalCreateUser(array('change own username'));
    $this->drupalLogin($user8);

    $user_edit_page = 'user/' . $user8->id() . '/edit';
    $this->drupalGet($user_edit_page);
    // Check for the form options.
    $domains = \Drupal::service('domain.storage')->loadMultiple();
    foreach ($domains as $domain) {
      $this->assertNoText($domain->label(), 'Domain form item not found.');
    }

    $this->assertNoText($label, 'All affiliates field not found.');

    // Change own username.
    $edit = array();
    $edit['name'] = $this->randomMachineName();

    $this->drupalPostForm($user_edit_page, $edit, t('Save'));
  }

}

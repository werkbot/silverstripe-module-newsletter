<?php

namespace Werkbot\Newsletter;

use PageController;
use SilverStripe\Dev\SapphireTest;

/**
 * Run with:
 * vendor/bin/phpunit vendor/werkbot/newsletter-module/tests/NewsletterPageControllerExtenderTest.php
 */
class NewsletterPageControllerExtenderTest extends SapphireTest
{
  protected $usesDatabase = true;

  /**
   * Test Insert To Newsletter
   */
  public function testInsertToNewsletter()
  {
    $this->assertEquals(true, PageController::create()->InsertToNewsletter("test1@test.com"));
    $this->assertEquals(true, PageController::create()->InsertToNewsletter("test2@test.com", "Test 2"));
    $this->assertEquals(true, PageController::create()->InsertToNewsletter("test3@test.com", "Test 3", "Test 3"));
  }
}

<?php

namespace Werkbot\Newsletter;

use MailchimpMarketing\ApiClient;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\FunctionalTest;

/**
 * Run with:
 * vendor/bin/phpunit vendor/werkbot/newsletter-module/tests/NewsletterTest.php
 */
class NewsletterTest extends FunctionalTest
{
  /**
   * Test Connection - Campaign Monitor
   * - Relys on CAMPAIGNMONITOR_API_KEY being set in env
   */
  public function testConnectionCampaignMonitor()
  {
    if (Environment::getEnv('CAMPAIGNMONITOR_API_KEY')) {
      $auth = array('api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY'));
      $wrap = new \CS_REST_General($auth);
      $result = $wrap->get_clients();
      $this->assertEquals(200, $result->http_status_code);
    } else {
      $this->markTestSkipped('api key not set in the env.');
    }
  }
  /**
   * Test Connection - Mailchimp
   */
  public function testConnectionMailchimp()
  {
    if (Environment::getEnv('MAILCHIMP_API_KEY') && Environment::getEnv('MAILCHIMP_SERVER_PREFIX')) {
      $mailchimp = new ApiClient();
      $mailchimp->setConfig([
        'apiKey' => Environment::getEnv('MAILCHIMP_API_KEY'),
        'server' => Environment::getEnv('MAILCHIMP_SERVER_PREFIX')
      ]);
      $response = $mailchimp->ping->getWithHttpInfo();
      $this->assertEquals("Everything's Chimpy!", $response->health_status);
    } else {
      $this->markTestSkipped('api key not set in the env.');
    }
  }
  /**
   * Test Connection - Constant Contact
   */
  /*public function testConnectionConstantContact()
  {
    if (Environment::getEnv('CONSTANTCONTACT_API_KEY') && Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN')) {
      $cc = new ConstantContact(Environment::getEnv('CONSTANTCONTACT_API_KEY'));

    } else {
      $this->markTestSkipped('api key not set in the env.');
    }
  }*/
  /**
   * Test Connection - Active Campaign
   */
  public function testConnectionActiveCampaign()
  {
    if (Environment::getEnv('ACTIVECAMPAIGN_URL') && Environment::getEnv('ACTIVECAMPAIGN_API_KEY')) {
      $ac = new \ActiveCampaign(Environment::getEnv('ACTIVECAMPAIGN_URL'), Environment::getEnv('ACTIVECAMPAIGN_API_KEY'));
      $this->assertEquals(true, $ac->credentials_test());
    } else {
      $this->markTestSkipped('api key not set in the env.');
    }
  }
}

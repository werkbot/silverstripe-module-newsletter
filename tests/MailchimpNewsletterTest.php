<?php

namespace Werkbot\Newsletter;

use MailchimpMarketing\ApiClient;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\FunctionalTest;

/**
 * Run with:
 * vendor/bin/phpunit vendor/werkbot/newsletter-module/tests/MailchimpNewsletterTest.php
 */
class MailchimpNewsletterTest extends FunctionalTest
{
  /**
   * Assert Failure
   * Utility method to automatically return a failed assertion for errors.
   */
  public function assertExceptionFailure($exception)
  {
    $message = $exception->getResponse()->getBody()->getContents();
    return $this->assertEquals(true, false, $message);
  }

  /**
   * Insert into newsletter
   * Inserts the given names into a list, runs assertions, and deletes the generated member.
   * Because this method does not begin with "test", it must be called by other "test" methods
   */
  public function InsertToNewsletter($firstName = '', $lastName = '')
  {
    if (Environment::getEnv('MAILCHIMP_API_KEY') && Environment::getEnv('MAILCHIMP_SERVER_PREFIX')) {
      $mailchimp = new ApiClient();
      $mailchimp->setConfig([
        'apiKey' => Environment::getEnv('MAILCHIMP_API_KEY'),
        'server' => Environment::getEnv('MAILCHIMP_SERVER_PREFIX')
      ]);
      try {
        $email = 'mailchimp-newsletter-test' . rand(0, 9999) .  '@email.com';

        // Get the first list
        $lists = $mailchimp->lists->getAllLists();
        if (isset($lists->lists[0])) {
          $list = $lists->lists[0];

          // Insert into newsletter list
          $insertResponse = $mailchimp->lists->setListMember($list->id, md5($email), [
            'email_address' => $email,
            'merge_fields' => [
              'FNAME' => $firstName,
              'LNAME' => $lastName,
            ],
            'status_if_new' => 'subscribed',
          ]);
          $response = $mailchimp->ping->getWithHttpInfo();
          $this->assertEquals("Everything's Chimpy!", $response->health_status);

          // Delete the new test list member
          echo PHP_EOL . 'Deleting the test member' . PHP_EOL;
          $subscriberHash = $insertResponse->id;
          $deleteResponse = $mailchimp->lists->deleteListMemberPermanent($list->id, $subscriberHash);
          $response = $mailchimp->ping->getWithHttpInfo();
          $this->assertEquals("Everything's Chimpy!", $response->health_status);

        }
      } catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->assertExceptionFailure($e);
        return;
      }
    } else {
      $this->markTestSkipped('API credentials not set in the env.');
    }
  }

  public function testConnection()
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
      $this->markTestSkipped('API credentials not set in the env.');
    }
  }

  public function testInsertWithNames()
  {
    return $this->InsertToNewsletter('MailchimpTestFirstname', 'MailchimpTestLastName');
  }

  public function testInsertWithoutNames()
  {
    return $this->InsertToNewsletter();
  }
}

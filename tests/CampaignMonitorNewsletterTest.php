<?php

namespace Werkbot\Newsletter;

use SilverStripe\Core\Environment;
use SilverStripe\Dev\FunctionalTest;

/**
 * Run with:
 * vendor/bin/phpunit vendor/werkbot/newsletter-module/tests/CampaignMonitorNewsletterTest.php
 */
class CampaignMonitorNewsletterTest extends FunctionalTest
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
   * Assert Campaign Monitor "result" Failure
   * Utility method to automatically return a failed assertion for unsuccessful Campaign Monitor results.
   */
  public function assertResultFailure($result)
  {
    $message = $result->response->Message;
    return $this->assertEquals(true, false, $message);
  }

  /**
   * Insert into newsletter
   * Inserts the given names into a list, runs assertions, and deletes the generated member.
   * Because this method does not begin with "test", it must be called by other "test" methods
   */
  public function InsertToNewsletter($firstName = '', $lastName = '')
  {
    if (Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID') && Environment::getEnv('CAMPAIGNMONITOR_API_KEY')) {
      $auth = ['api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY')];
      try {
        $clientsWrap = new \CS_REST_Clients(Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID'), $auth);
        $listsResult = $clientsWrap->get_lists();
        if ($listsResult->was_successful()) {
          $this->assertEquals(200, $listsResult->http_status_code);
        } else {
          $this->assertResultFailure($listsResult);
          return;
        }
        if (count($listsResult->response)) {
          $listID = $listsResult->response[0]->ListID;
        }

        $subscribersWrap = new \CS_REST_Subscribers($listID, $auth);
        $email = 'campaignmonitor-newsletter-test' . rand(0, 9999) .  '@email.com';
        $data = [
          'EmailAddress' => $email,
          'Name' => $firstName . ' ' . $lastName,
          'ConsentToTrack' => 'yes',
          'Resubscribe' => true
        ];

        $addSubscriberResult = $subscribersWrap->add($data);
        if ($addSubscriberResult->was_successful()) {
          $this->assertEquals(201, $addSubscriberResult->http_status_code); // 201 "Created"

          echo PHP_EOL . 'Deleting the test member' . PHP_EOL;
          $deleteSubscriberResult = $subscribersWrap->delete($email);
          $this->assertEquals(200, $deleteSubscriberResult->http_status_code);

        } else {
          $this->assertResultFailure($addSubscriberResult);
          return;
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
    if (Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID') && Environment::getEnv('CAMPAIGNMONITOR_API_KEY')) {
      $auth = ['api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY')];
      $wrap = new \CS_REST_Clients(Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID'), $auth);

      $result = $wrap->get_lists();
      if ($result->was_successful()) {
        $this->assertEquals(200, $result->http_status_code);
      } else {
        $this->assertResultFailure($result);
        return;
      }
    } else {
      $this->markTestSkipped('API credentials not set in the env.');
    }
  }

  public function testInsertWithNames()
  {
    return $this->InsertToNewsletter('CampaignMonitorTestFirstname', 'CampaignMonitorTestLastName');
  }

  public function testInsertWithoutNames()
  {
    return $this->InsertToNewsletter();
  }
}

<?php

namespace Werkbot\Newsletter;

use Ctct\ConstantContact;
use MailchimpMarketing\ApiClient;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

/**
 * A SiteConfig extension that adds global newsletter settings to your site.
 */
class NewsletterSettings extends DataExtension
{

  private static $db = [
    'NewsletterAPI' => "Enum('campaignmonitor,mailchimp,constantcontact,activecampaign,redtail,none', 'none')",
    "NewsletterSuccessText" => "HTMLText",
    "NewsletterErrorText" => "HTMLText",

    'CampaignMonitorListID' => "Text",
    'MailchimpListID' => "Text",
    'ConstantContactListID' => "Text",
    'ActiveCampaignListID' => "Text"
  ];

  public function updateCMSFields(FieldList $fields)
  {
    $fields->findOrMakeTab('Root.Newsletter', 'Newsletter');

    $fields->addFieldToTab(
      "Root.Newsletter",
      OptionsetField::create(
        'NewsletterAPI',
        'Select your newsletter API',
        array(
          'campaignmonitor' => 'Campaign Monitor',
          'mailchimp' => 'Mail Chimp',
          'constantcontact' => 'Constant Contact',
          'activecampaign' => 'Active Campaign',
          'redtail' => 'Redtail',
          'none' => 'No API'
        ),
        'none'
      )
    );

    // Campaign Monitor
    if (Environment::getEnv('CAMPAIGNMONITOR_API_KEY') && Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID')) {
      try {
        $auth = array('api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY'));
        $wrap = new \CS_REST_Clients(Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID'), $auth);
        $result = $wrap->get_lists();
        if (!$result->was_successful()) {
          throw new \Exception('The connection to Campaign Monitor could not be established.');
        }
        $listarray = array();
        foreach ($result->response as $list) {
          $listarray[$list->ListID] = $list->Name;
        }
        $CampaignMonitorListID = DropdownField::create("CampaignMonitorListID", "Select a list", $listarray)
          ->displayIf("NewsletterAPI")->isEqualTo("campaignmonitor")->end();
        $fields->addFieldToTab('Root.Newsletter', $CampaignMonitorListID);
      } catch(\Exception $e) {
        $this->showIntegrationWarning($e->getMessage());
      }
    }

    // Mailchimp
    if (Environment::getEnv('MAILCHIMP_API_KEY') && Environment::getEnv('MAILCHIMP_SERVER_PREFIX')) {
      try {
        $mailchimp = new ApiClient();
        $mailchimp->setConfig([
          'apiKey' => Environment::getEnv('MAILCHIMP_API_KEY'),
          'server' => Environment::getEnv('MAILCHIMP_SERVER_PREFIX')
        ]);
        $response = $mailchimp->lists->getAllLists();
        $listarray = array();
        foreach ($response->lists as $list) {
          $listarray[$list->id] = $list->name;
        }
        $MailchimpListID = DropdownField::create("MailchimpListID", "Select a list", $listarray)
          ->displayIf("NewsletterAPI")->isEqualTo("mailchimp")->end();
        $fields->addFieldToTab('Root.Newsletter', $MailchimpListID);
      } catch(\GuzzleHttp\Exception\ClientException $e) {
        $this->showIntegrationWarning('The connection to Mailchimp could not be established.');
      }
    }

    // Constant Contact
    if (Environment::getEnv('CONSTANTCONTACT_API_KEY') && Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN')) {
      try {
        $cc = new ConstantContact(Environment::getEnv('CONSTANTCONTACT_API_KEY'));
        $lists = $cc->getLists(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'));
        $listarray = array();
        foreach ($lists as $list) {
          $listarray[$list->id] = $list->name;
        }
        $ConstantContactListID = DropdownField::create("ConstantContactListID", "Select a list", $listarray)
          ->displayIf("NewsletterAPI")->isEqualTo("constantcontact")->end();
        $fields->addFieldToTab('Root.Newsletter', $ConstantContactListID);
      } catch(\Ctct\Exceptions\CtctException $e) {
        $this->showIntegrationWarning('The connection to Constant Contact could not be established. ' . $e->getErrors()[0]['error_message']);
      }
    }

    // Active Campaign
    if (Environment::getEnv('ACTIVECAMPAIGN_API_KEY') && Environment::getEnv('ACTIVECAMPAIGN_URL')) {
      try {
        // Active Campaign List Select
        $ac = new \ActiveCampaign(Environment::getEnv('ACTIVECAMPAIGN_URL'), Environment::getEnv('ACTIVECAMPAIGN_API_KEY'));
        if (!$ac->credentials_test()) {
          throw new \Exception('The connection to Active Campaign could not be established.');
        }
        // Adjust the default cURL timeout
        $ac->set_curl_timeout(10);
        $params = [
          'ids'  => 'all',
        ];
        $lists = $ac->api("list/list_", $params);
        $listarray = [];
        foreach ($lists as $list) {
          if (is_object($list)) {
            $listarray[$list->id] = $list->name;
          }
        }
        $ActiveCampaignListID = DropdownField::create("ActiveCampaignListID", "Select a list", $listarray)
          ->displayIf("NewsletterAPI")->isEqualTo("activecampaign")->end();
        $fields->addFieldToTab('Root.Newsletter', $ActiveCampaignListID);
      } catch(\Exception $e) {
        $this->showIntegrationWarning($e->getMessage());
      }
    }

    // Success text
    $htmlField = new HTMLEditorField('NewsletterSuccessText', 'Success/Thankyou Text');
    $htmlField->addExtraClass('stacked');
    $htmlField->setRows(5);
    $fields->addFieldToTab('Root.Newsletter', $htmlField);

    // Error text
    $htmlField = new HTMLEditorField('NewsletterErrorText', 'Error Text');
    $htmlField->addExtraClass('stacked');
    $htmlField->setRows(5);
    $fields->addFieldToTab('Root.Newsletter', $htmlField);
  }

  /**
   * Show warning if integration connection fails.
   *
   * @param string $message - The warning message.
   */
  public function showIntegrationWarning($message)
  {
    $script = '';
    $scriptTpl = <<<EOT
        jQuery.noticeAdd({
          text: '%s',
          stay: true,
          type: '%s'
        });
    EOT;
    $msg = [
      'type' => 'warning',
      'message' => _t(
          __CLASS__ . '.DevModeWarning',
          $message
      )
    ];
    $script = sprintf($scriptTpl, $msg[ 'message' ], $msg[ 'type' ]);

    Requirements::customScript($script);
  }
}

<?php

namespace Werkbot\Newsletter;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use CS_REST_Subscribers;
use GuzzleHttp\Exception\ClientException;
use ActiveCampaign;
use Ctct\ConstantContact;
use SilverStripe\Forms\Form;
use MailchimpMarketing\ApiClient;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use Ctct\Exceptions\CtctException;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormAction;
use Ctct\Components\Contacts\Contact;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * A PageController extension that handles newsletter related requests.
 */
class NewsletterPageControllerExtender extends Extension
{
  private static $allowed_actions = [
    "NewsletterForm",
    "InsertToNewsletter",
  ];

  /**
   * The Newsletter Form
   *
   * @return Form $Form
   */
  public function NewsletterForm()
  {
    if (!$this->getOwner()->NewsletterShowHide) {
      return false;
    }
    $config = SiteConfig::current_site_config();

    if ($this->getOwner()->isAjax) {
      return $this->getOwner()->ProcessNewsletterForm($_POST);
    } else {
      //FIELDS
      $fields = FieldList::create(EmailField::create("Email", _t("NewsletterForm.LABEL", "Enter Email"))
        ->setAttribute('title', _t("NewsletterForm.TITLE", "Enter Your Email"))
        ->setAttribute('placeholder', _t("NewsletterForm.PLACEHOLDER", "Enter Your Email")), TextField::create("FirstName", _t("NewsletterForm.LABEL_FIRSTNAME", "First Name"))
        ->setAttribute('title', _t("NewsletterForm.TITLE_FIRSTNAME", "Enter Your First Name"))
        ->setAttribute('placeholder', _t("NewsletterForm.PLACEHOLDER_FIRSTNAME", "Enter Your First Name")), TextField::create("LastName", _t("NewsletterForm.LABEL_LASTNAME", "Last Name"))
        ->setAttribute('title', _t("NewsletterForm.TITLE_LASTNAME", "Enter Your Last Name"))
        ->setAttribute('placeholder', _t("NewsletterForm.PLACEHOLDER_LASTNAME", "Enter Your Last Name")));

      //ACTIONS
      $actionbutton = FormAction::create('ProcessNewsletterForm', _t("NewsletterForm.ACTION", "Sign Up"))
        ->setUseButtonTag(true)
        ->setAttribute("aria-label", _t("NewsletterForm.ACTION", "Sign Up"));
      $actions = FieldList::create($actionbutton);

      //VALIDATORS
      $validator = RequiredFieldsValidator::create('Email');

      $this->getOwner()->extend('updateNewsletterFormFields', $fields);

      //CREATE THE FORM
      $Form = Form::create($this->getOwner(), "NewsletterForm", $fields, $actions, $validator);

      // Enable Recaptcha if enabled
      if (Environment::getEnv('NEWSLETTER_RECAPTCHA')) {
        $Form->enableSpamProtection();
      }

      $data = array();
      $Form->customise($data)->setTemplate(['Forms\\NewsletterForm', 'NewsletterForm']);

      return $Form;
    }
  }

  /**
   * Processes the newsletter form submit.
   *
   * @param array $data - Form data in the form of an array
   * @param Form $data - The newsletter form instance
   * @return DBHTMLText|HTTPResponse
   */
  public function ProcessNewsletterForm($data, Form $form)
  {
    $email = isset($data["Email"]) ? $data["Email"] : "";
    $firstname = isset($data["FirstName"]) ? $data["FirstName"] : "";
    $lastname = isset($data["LastName"]) ? $data["LastName"] : "";

    $status = $this->getOwner()->InsertToNewsletter($email, $firstname, $lastname);

    $resultdata = [
      "NewsletterMessage" => "",
      "MessageType" => "",
    ];

    $this->getOwner()->extend("updateProcessNewsletterForm", $data);

    if ($status) {
      //SHOW SUCCESS PAGE
      if ($this->getOwner()->SiteConfig->NewsletterSuccessText) {
        $resultdata["NewsletterMessage"] = $this->getOwner()->SiteConfig->NewsletterSuccessText;
      } else {
        $resultdata["NewsletterMessage"] = 'Thank you for subscribing!';
      }
      $resultdata["MessageType"] = "good";

      return $this->getOwner()->customise($resultdata)->renderWith(array($this->getOwner()->ClassName, 'Page', 'NewsletterFormSubmission'));
    } else {
      //SHOW ERROR PAGE
      if ($this->getOwner()->SiteConfig->NewsletterErrorText) {
        $resultdata["NewsletterMessage"] = $this->getOwner()->SiteConfig->NewsletterErrorText;
      } else {
        $resultdata["NewsletterMessage"] = 'There was an issue processing your request.';
      }
      $resultdata["MessageType"] = "bad";

      return $this->getOwner()->customise($resultdata)->renderWith(array($this->getOwner()->ClassName, 'Page', 'NewsletterFormSubmission'));
    }
  }

  /**
   * Creates a new NewsletterSubmission and inserts subscriber info into optional thirdparty mailing service
   *
   * @param string $Email - The subscriber's email address
   * @param string $FirstName - The subscriber's first name
   * @param string $LastName - The subscriber's last name
   * @return boolean $status - The success status, true being successful
   */
  public function InsertToNewsletter($Email, $FirstName = "", $LastName = "")
  {
    $status = true;
    $config = SiteConfig::current_site_config();

    // Insert Into Campaign Monitor
    if ($config->NewsletterAPI == "campaignmonitor" && $config->CampaignMonitorListID) {
      $auth = array('api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY'));
      $wrap = new CS_REST_Subscribers($config->CampaignMonitorListID, $auth);

      $data = array(
        'EmailAddress' => $Email,
        'Name' => $FirstName . " " . $LastName,
        'ConsentToTrack' => 'yes',
        'Resubscribe' => true
      );

      $this->getOwner()->extend("updateCampaignMonitorNewsletter", $data);
      $result = $wrap->add($data);
    }

    // Insert Into Mailchimp
    if ($config->NewsletterAPI == "mailchimp" && $config->MailchimpListID) {
      $mailchimp = new ApiClient();
      $mailchimp->setConfig([
        'apiKey' => Environment::getEnv('MAILCHIMP_API_KEY'),
        'server' => Environment::getEnv('MAILCHIMP_SERVER_PREFIX')
      ]);
      try {
        $data = [
          "email_address" => $Email,
          "merge_fields" => [
            "FNAME" => $FirstName,
            "LNAME" => $LastName,
          ],
          "status_if_new" => "subscribed",
        ];

        $this->getOwner()->extend('updateMailchimpNewsletter', $data);
        $response = $mailchimp->lists->setListMember($config->MailchimpListID, md5($Email), $data);
      } catch (ClientException $e) {
        // Issue while adding
        $status = false;
      }
    }

    // Insert Into Constant Contact
    if (
      $config->NewsletterAPI == "constantcontact"
      && Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN')
      && Environment::getEnv('CONSTANTCONTACT_API_KEY')
    ) {

      $cc = new ConstantContact(Environment::getEnv('CONSTANTCONTACT_API_KEY'));

      // attempt to fetch lists in the account, catching any exceptions and printing the errors to screen
      try {
        $lists = $cc->getLists(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'));
      } catch (CtctException $ex) {
        foreach ($ex->getErrors() as $error) {
          print_r($error);
        }
        if (!isset($lists)) {
          $lists = null;
        }
      }

      // check if the form was submitted
      if (isset($Email) && strlen($Email) > 1) {
        $action = "Getting Contact By Email Address";
        try {
          // check to see if a contact with the email address already exists in the account
          $response = $cc->getContacts(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'), array("email" => $Email));

          // create a new contact if one does not exist
          if (empty($response->results)) {
            $action = "Creating Contact";

            $contact = new Contact();
            $contact->addEmail($Email);
            $contact->addList($config->ConstantContactListID);
            $contact->first_name = $FirstName;
            $contact->last_name = $LastName;

            $this->getOwner()->extend("updateCreateConstantContactNewsletter", $contact);

            $returnContact = $cc->addContact(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'), $contact, true);
          } else { // update the existing contact if address already existed
            $action = "Updating Contact";

            $contact = $response->results[0];
            if ($contact instanceof Contact) {
              $contact->addList($config->ConstantContactListID);

              $this->getOwner()->extend("updateConstantContactNewsletter", $contact);

              $returnContact = $cc->updateContact(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'), $contact, true);
            } else {
              $e = new CtctException();
              $e->setErrors(array("type", "Contact type not returned"));
              throw $e;
            }
          }
        } catch (CtctException $ex) { // catch any exceptions thrown during the process and print the errors to screen
          echo '<span class="label label-important">Error ' . $action . '</span>';
          echo '<div class="container alert-error"><pre class="failure-pre">';
          print_r($ex->getErrors());
          echo '</pre></div>';
          die();
        }
      }
    }

    // Insert Into Active Campaign
    if (
      $config->NewsletterAPI == "activecampaign"
      && Environment::getEnv('ACTIVECAMPAIGN_URL')
      && Environment::getEnv('ACTIVECAMPAIGN_API_KEY')
    ) {
      // Add this user to Active Campaign
      $ac = new ActiveCampaign(Environment::getEnv('ACTIVECAMPAIGN_URL'), Environment::getEnv('ACTIVECAMPAIGN_API_KEY'));
      if ($ac->credentials_test()) {
        $contact = [
          "email" => $Email,
          "first_name" => $FirstName,
          "last_name" => $LastName,
          "p[{$config->ActiveCampaignListID}]" => $config->ActiveCampaignListID,
          "status[{$config->ActiveCampaignListID}]" => 1, // "Active" status
        ];

        $this->getOwner()->extend("updateActiveCampaignContactNewsletter", $contact);

        $contact_sync = $ac->api("contact/sync", $contact);
      }
    }

    // Insert Into Redtail
    if (
      $config->NewsletterAPI == "redtail"
      && Environment::getEnv('REDTAIL_URL')
      && Environment::getEnv('REDTAIL_API_KEY')
      && Environment::getEnv('REDTAIL_USER')
      && Environment::getEnv('REDTAIL_PASSWORD')
    ) {
      $REDTAIL_URL = Environment::getEnv('REDTAIL_URL');
      $REDTAIL_API_KEY = Environment::getEnv('REDTAIL_API_KEY');
      $REDTAIL_USER = Environment::getEnv('REDTAIL_USER');
      $REDTAIL_PASSWORD = Environment::getEnv('REDTAIL_PASSWORD');

      // Encoded username/pass
      $encodedAuth = base64_encode("$REDTAIL_API_KEY:$REDTAIL_USER:$REDTAIL_PASSWORD");

      // Add contact to Redtail
      $contact = NewsletterCurlHelper::Instance()->callCurl($REDTAIL_URL . '/contacts', json_encode([
        'type' => 'Crm::Contact::Individual',
        'first_name' => $FirstName,
        'last_name' => $LastName,
      ]), $encodedAuth);

      // Add email to Redtail
      $contactEmail = NewsletterCurlHelper::Instance()->callCurl($REDTAIL_URL . '/contacts/' . $contact->contact->id . '/emails', json_encode([
        'email_type' => 3, // 1: "Home", 2: "Work", 3: "Other"
        'address' => $Email,
      ]), $encodedAuth);
    }

    //SAVE SUBMISSION NO MATTER WHAT API (or if none)
    $submission = NewsletterSubmission::create();
    $submission->Email = $Email;
    $submission->FirstName = $FirstName;
    $submission->LastName = $LastName;
    $submission->write();

    $this->getOwner()->extend('onAfterInsertToNewsletter', $submission);

    return $status;
  }

}


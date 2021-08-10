<?php
/**/
use Ctct\ConstantContact;
use SilverStripe\Forms\Form;
use MailchimpMarketing\ApiClient;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use Ctct\Exceptions\CtctException;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataExtension;
use Ctct\Components\Contacts\Contact;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
/**/
class NewsletterPageControllerExtender extends DataExtension {
	/**/
	private static $allowed_actions = [
		"NewsletterForm",
    "InsertToNewsletter",
	];
	/**/
	public function NewsletterForm(){
    if(!$this->owner->NewsletterShowHide){
      return false;
    }
		$config = SiteConfig::current_site_config();

		if ($this->owner->isAjax) {
			return $this->owner->ProcessNewsletterForm($_POST);
		}else{
			//FIELDS
			$fields = new FieldList(
				EmailField::create("Email", _t("NewsletterForm.LABEL", "Enter Email"))
					->setAttribute('title', _t("NewsletterForm.TITLE", "Enter Your Email"))
					->setAttribute('placeholder', _t("NewsletterForm.PLACEHOLDER", "Enter Your Email")),

				TextField::create("FirstName", _t("NewsletterForm.LABEL_FIRSTNAME", "First Name"))
          ->setAttribute('title', _t("NewsletterForm.TITLE_FIRSTNAME", "Enter Your First Name"))
          ->setAttribute('placeholder', _t("NewsletterForm.PLACEHOLDER_FIRSTNAME", "Enter Your First Name")),

        TextField::create("LastName", _t("NewsletterForm.LABEL_LASTNAME", "Last Name"))
          ->setAttribute('title', _t("NewsletterForm.TITLE_LASTNAME", "Enter Your Last Name"))
          ->setAttribute('placeholder', _t("NewsletterForm.PLACEHOLDER_LASTNAME", "Enter Your Last Name"))
			);

			//ACTIONS
      $actionbutton = FormAction::create('ProcessNewsletterForm', _t("NewsletterForm.ACTION", "Sign Up"))
        ->setUseButtonTag(true)
        ->setAttribute("aria-label", _t("NewsletterForm.ACTION", "Sign Up"));
  		$actions = new FieldList(
  			$actionbutton
  		);

			//VALIDATORS
			$validator = new RequiredFields('Email');

			//CREATE THE FORM
			$Form = new Form($this->owner, "NewsletterForm", $fields, $actions, $validator);

      // Enable Recaptcha if enabled
      if(Environment::getEnv('NEWSLETTER_RECAPTCHA')){
        $Form->enableSpamProtection();
      }

      $data = array();
      $Form->customise($data)->setTemplate(['Forms\\NewsletterForm', 'NewsletterForm']);

			return $Form;
		}
	}
	/**/
	public function ProcessNewsletterForm($data, Form $form){

    $status = $this->owner->InsertToNewsletter($data["Email"], $data["FirstName"], $data["LastName"]);

    $resultdata = [
      "NewsletterMessage" => "",
      "MessageType" => "",
    ];

    $this->owner->extend("updateProcessNewsletterForm", $data);

		if($status){
      //SHOW SUCCESS PAGE
      $resultdata["NewsletterMessage"] = $this->owner->SiteConfig->NewsletterSuccessText;
      $resultdata["MessageType"] = "good";

      return $this->owner->customise($resultdata)->renderWith(array($this->owner->ClassName, 'Page', 'NewsletterFormSubmission'));
		}else{
			//SHOW ERROR PAGE
      $resultdata["NewsletterMessage"] = $this->owner->SiteConfig->NewsletterErrorText;
      $resultdata["MessageType"] = "bad";

			return $this->owner->customise($resultdata)->renderWith(array($this->owner->ClassName, 'Page', 'NewsletterFormSubmission'));
		}
	}
  /**/
  public function InsertToNewsletter($Email, $FirstName="", $LastName=""){
    $status = true;
  	$config = SiteConfig::current_site_config();

    // Insert Into Campaign Monitor
    if($config->NewsletterAPI=="campaignmonitor" && $config->CampaignMonitorListID){
      $auth = array('api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY'));
      $wrap = new CS_REST_Subscribers($config->CampaignMonitorListID, $auth);
      $result = $wrap->add(array(
        'EmailAddress' => $Email,
        'Name' => $FirstName." ".$LastName,
        'ConsentToTrack' => 'yes',
        'Resubscribe' => true
      ));
    }

    // Insert Into Mailchimp
    if($config->NewsletterAPI=="mailchimp" && $config->MailchimpListID){
      $mailchimp = new ApiClient();
      $mailchimp->setConfig([
        'apiKey' => Environment::getEnv('MAILCHIMP_API_KEY'),
        'server' => Environment::getEnv('MAILCHIMP_SERVER_PREFIX')
      ]);
      try{
        $response = $mailchimp->lists->setListMember($config->MailchimpListID, md5($Email), [
          "email_address" => $Email,
          "merge_fields" => [
            "FNAME" => $FirstName,
            "LNAME" => $LastName,
          ],
          "status_if_new" => "subscribed",
        ]);
      }catch (GuzzleHttp\Exception\ClientException $e) {
        // Issue while adding
        $status = false;
      }
    }

    // Insert Into Constant Contact
    if($config->NewsletterAPI=="constantcontact"
      && Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN')
      && Environment::getEnv('CONSTANTCONTACT_API_KEY')
    ){

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

                  $returnContact = $cc->addContact(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'), $contact, true);

              } else { // update the existing contact if address already existed
                  $action = "Updating Contact";

                  $contact = $response->results[0];
                  if ($contact instanceof Contact) {
                      $contact->addList($config->ConstantContactListID);

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
    if($config->NewsletterAPI=="activecampaign"
      && Environment::getEnv('ACTIVECAMPAIGN_URL')
      && Environment::getEnv('ACTIVECAMPAIGN_API_KEY')
    ){
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
        $contact_sync = $ac->api("contact/sync", $contact);
      }
    }

    //SAVE SUBMISSION NO MATTER WHAT API (or if none)
    $submission = new NewsletterSubmission();
    $submission->Email = $Email;
    $submission->FirstName = $FirstName;
    $submission->LastName = $LastName;
    $submission->write();

    return $status;
  }
}

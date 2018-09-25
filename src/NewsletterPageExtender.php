<?php
/**/
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataExtension;
/**/
class NewsletterPageExtender extends DataExtension {
	/**/
	private static $allowed_actions = [
		"NewsletterForm",
    "InsertToNewsletter",
	];
	/**/
	public function NewsletterForm(){

		//
		$config = SiteConfig::current_site_config();

		if ($this->owner->isAjax) {
			return $this->owner->ProcessNewsletterForm($_POST);
		}else{
			//FIELDS
			$fields = new FieldList(
				EmailField::create("Email", "Email")
					->setAttribute('title', "Join Our Newsletter")
					->setAttribute('placeholder', "Enter your email")
			);

			//ACTIONS
			$actionText = (($config->NewsletterFormButtonText) ? $config->NewsletterFormButtonText : "Sign Up");
      $actionbutton = FormAction::create('ProcessNewsletterForm', $actionText)
        ->setUseButtonTag(true);
  		$actions = new FieldList(
  			$actionbutton
  		);

			//VALIDATORS
			$validator = new RequiredFields('Email');

			//CREATE THE FORM
			$Form = new Form($this->owner, "NewsletterForm", $fields, $actions, $validator);
		    $data = array();
      $Form->customise($data)->setTemplate('NewsletterForm');

			return $Form;
		}
	}
	/**/
	public function ProcessNewsletterForm($data, Form $form){

    $status = $this->owner->InsertToNewsletter($data["Email"]);

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
  public function InsertToNewsletter($Email){
    $status = true;

    //SAVE SUBMISSION NO MATTER WHAT API (or if none)
    $submission = new NewsletterSubmission();
    $submission->Email = $Email;
    $submission->write();

    return $status;
  }
}

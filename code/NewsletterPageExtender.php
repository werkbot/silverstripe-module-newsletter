<?php
/**/
use \DrewM\MailChimp\MailChimp;
/**/
class NewsletterPageExtender extends DataExtension {
	/**/
	private static $allowed_actions = array (
		"NewsletterForm",
    "InsertToNewsletter"
	);
	/**/
	public function NewsletterForm(){

		//
		$config = SiteConfig::current_site_config();

		if ($this->owner->isAjax) {
			return $this->owner->ProcessNewsletterForm($_POST);
		}else{
			//FIELDS
			if($this->owner->SiteConfig->NewsletterAPI=='mailchimp'){
				if($this->owner->SiteConfig->MailchimpCaptureFirstName){
					$FirstName = TextField::create("FirstName", "First Name")
					->setAttribute('placeholder', "Enter your First Name");
				}else{
					$FirstName = HiddenField::create("FirstName", "First Name");
				}
				if($this->owner->SiteConfig->MailchimpCaptureLastName){
					$LastName = TextField::create("LastName", "Last Name")
					->setAttribute('placeholder', "Enter your Last Name");
				}else{
					$LastName = HiddenField::create("LastName", "Last Name");
				}
				$fields = new FieldList(
					EmailField::create("Email", "Email")
						->setAttribute('title', "Join Our Newsletter")
						->setAttribute('placeholder', "Enter your Email"),
					$FirstName,
					$LastName
				);
			}else if($this->owner->SiteConfig->NewsletterAPI=='constantcontact' || $this->owner->SiteConfig->NewsletterAPI=='none'){
				$fields = new FieldList(
					EmailField::create("Email", "Email")
						->setAttribute('title', "Join Our Newsletter")
						->setAttribute('placeholder', "Enter your email")
				);
			}

			//ACTIONS
			$actionText = (($config->NewsletterFormButtonText) ? $config->NewsletterFormButtonText : "Sign Up");
			$actions = new FieldList (
				FormAction::create("ProcessNewsletterForm")->setTitle($actionText)
			);

			//VALIDATORS
			$validator = new RequiredFields('Email');

			//CREATE THE FORM
			$Form = new Form($this->owner, "NewsletterForm", $fields, $actions, $validator);

			return $Form;
		}
	}
	/**/
	public function ProcessNewsletterForm($data, Form $form){

    $FirstName = "";
    if(isset($data["FirstName"])){
      $FirstName = $data["FirstName"];
    }
    $LastName = "";
    if(isset($data["LastName"])){
      $LastName = $data["LastName"];
    }

    $status = $this->InsertToNewsletter($data["Email"], $FirstName, $LastName);

		if($status){
      //SHOW SUCCESS PAGE
      $data = array(
        "NewsletterMessage" => $this->owner->SiteConfig->NewsletterSuccessText,
        "MessageType" => "good"
      );
      return $this->owner->customise($data)->renderWith(array($this->owner->ClassName, 'Page', 'NewsletterFormSubmission'));
		}else{
			//SHOW ERROR PAGE
			$data = array(
				"NewsletterMessage" => $this->owner->SiteConfig->NewsletterErrorText,
				"MessageType" => "bad"
			);
			return $this->owner->customise($data)->renderWith(array($this->owner->ClassName, 'Page', 'NewsletterFormSubmission'));
		}
	}
  /**/
  public function InsertToNewsletter($Email, $FirstName="", $LastName=""){
    if($this->owner->SiteConfig->NewsletterAPI=='mailchimp'){
      require_once(MAILCHIMP_INCLUDES.'/MailChimp.php');
			$MailChimp = new MailChimp($this->owner->SiteConfig->MailchimpApikey);
      $result = $MailChimp->post("lists/".$this->owner->SiteConfig->MailchimpListName."/members", [
				'email_address' => $Email,
        'merge_fields' => ['FNAME'=> $FirstName, 'LNAME'=> $LastName],
				'status'        => 'subscribed',
			]);
      if ($MailChimp->success()) {
        $status = true;
      }else{
        $status = false;
      }
    }else if($this->owner->SiteConfig->NewsletterAPI=='constantcontact'){
      $api = new cc($this->owner->SiteConfig->ConstantContactUsername, $this->owner->SiteConfig->ConstantContactPassword, $this->owner->SiteConfig->ConstantContactApikey);
      $contact = $api->query_contacts($data["Email"]);
      if($contact){
        //UPDATE CONTACT
        $this->cc_status = $api->update_contact($contact['id'], $Email, $this->owner->SiteConfig->ConstantContactListName);
        if($this->cc_status){
          $status = true;
        }else{
          $status = false;
        }
      }else{
        //ADD CONTACT
        $extra_fields = array();
        $this->cc_status = $api->create_contact($Email, $this->owner->SiteConfig->ConstantContactListName, $extra_fields);
        if($this->cc_status){
          $status = true;
        }else{
          $status = false;
        }
      }
    } else if ($this->owner->SiteConfig->NewsletterAPI=='none') {
      $status = true;
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

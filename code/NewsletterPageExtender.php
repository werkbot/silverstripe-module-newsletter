<?php
/**/
class NewsletterPageExtender extends DataExtension {
	/**/
	private static $allowed_actions = array (
		"NewsletterForm"
	);
	/**/
	public function NewsletterForm(){

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
			$actions = new FieldList (
				FormAction::create("ProcessNewsletterForm")->setTitle("Sign up")
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
		if($this->owner->SiteConfig->NewsletterAPI=='mailchimp'){
			$api = new MailChimp($this->owner->SiteConfig->MailchimpApikey);
			$result = $api->call('lists/subscribe', array(
				'id'				=> $this->owner->SiteConfig->MailchimpListName,
				'email'             => array('email' => $data["Email"]),
				'merge_vars'        => array('FNAME' => $data["FirstName"], 'LNAME' => $data["LastName"]),
				'double_optin'      => false,
				'update_existing'   => true,
				'replace_interests' => false,
				'send_welcome'      => false
			));
			$status = isset($result["status"]);
		}else if($this->owner->SiteConfig->NewsletterAPI=='constantcontact'){
			$api = new cc($this->owner->SiteConfig->ConstantContactUsername, $this->owner->SiteConfig->ConstantContactPassword, $this->owner->SiteConfig->ConstantContactApikey);
			$contact = $api->query_contacts($data["Email"]);
			if($contact){
				//UPDATE CONTACT
				$this->cc_status = $api->update_contact($contact['id'], $data["Email"], $this->owner->SiteConfig->ConstantContactListName);
				if($this->cc_status){
					$status = false;
				}else{
					$status = true;
				}
			}else{
				//ADD CONTACT
				$extra_fields = array();
				$this->cc_status = $api->create_contact($data["Email"], $this->owner->SiteConfig->ConstantContactListName, $extra_fields);
				if($this->cc_status){
					$status = false;
				}else{
					$status = true;
				}
			}
		} else if ($this->owner->SiteConfig->NewsletterAPI=='none') {
			$status = false;
		}

		//SAVE SUBMISSION NO MATTER WHAT API (or if none)
		$submission = new NewsletterSubmission();
		$submission->Email = $data["Email"];
		$submission->FirstName = ((isset($data["FirstName"])) ? $data["FirstName"] : "");
		$submission->LastName = ((isset($data["LastName"])) ? $data["LastName"] : "");
		$submission->write();

		if($status){
			//SHOW ERROR PAGE
			$data = array(
				"Content" => $this->owner->SiteConfig->NewsletterErrorText
			);
			return $this->owner->customise($data)->renderWith(array('Page', 'NewsletterFormSubmission'));
		}else{
			//SHOW SUCCESS PAGE
			$data = array(
				"Content" => $this->owner->SiteConfig->NewsletterSuccessText
			);
			return $this->owner->customise($data)->renderWith(array('Page', 'NewsletterFormSubmission'));
		}
	}
}

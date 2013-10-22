<?php
/**/
class MailchimpPageExtender extends DataExtension {
	/**/
	private static $allowed_actions = array (
		"NewsletterForm"
	);
	/**/
	public function NewsletterForm(){
		//FIELDS
		if($this->owner->SiteConfig->MailchimpCaptureFirstName){
			$FirstName = TextField::create("FirstName", "First Name");
		}else{
			$FirstName = HiddenField::create("FirstName", "First Name");	
		}
		if($this->owner->SiteConfig->MailchimpCaptureLastName){
			$LastName = TextField::create("LastName", "Last Name");
		}else{
			$LastName = HiddenField::create("LastName", "Last Name");	
		}
		$fields = new FieldList(
			EmailField::create("Email", "Email"),
			$FirstName,
			$LastName
		);

		//ACTIONS
		$actions = new FieldList (
			FormAction::create("ProcessMailChimpForm")->setTitle("Signup")
		);
		
		//VALIDATORS
	    $validator = new RequiredFields('Email');
		
		//CREATE THE FORM
		$Form = new Form($this->owner, "NewsletterForm", $fields, $actions, $validator);  
		
		return $Form;
	}
	/**/
	public function ProcessMailChimpForm($data, Form $form){
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
		if(isset($result["status"])){
			if($result["status"]=="error"){
				//SHOW ERROR PAGE
				$data = array(
					"Content" => $this->owner->SiteConfig->MailchimpErrorText
				);
				return $this->owner->customise($data)->renderWith(array('Page'));
			}
		}else{
			//SHOW SUCCESS PAGE
			$data = array(
				"Content" => $this->owner->SiteConfig->MailchimpSuccessText
			);
			return $this->owner->customise($data)->renderWith(array('Page'));
		}
	}
}
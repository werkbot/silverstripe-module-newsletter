<?php
	/**/
	class NewsletterSettings extends DataExtension {
		/**/
		public static $db = array( 
			'NewsletterAPI' => "Enum('constantcontact,mailchimp', 'mailchimp')",
			
			'MailchimpApikey' => 'Text',
			'MailchimpListName' => 'Text',
    		"MailchimpCaptureFirstName" => "Boolean",
    		"MailchimpCaptureLastName" => "Boolean",
			
			'ConstantContactApikey' => 'Text',
			'ConstantContactListName' => 'Text',
			'ConstantContactUsername' => 'Text',
			'ConstantContactPassword' => 'Text',
			
    		"NewsletterSuccessText" => "HTMLText",
    		"NewsletterErrorText" => "HTMLText",
		);
		/**/
		public static $defaults = array( 
    		"MailchimpCaptureFirstName" => "0",
    		"MailchimpCaptureLastName" => "0"
		);
		/**/
        public function updateCMSFields(FieldList $fields) {
			/**/
			$fields->findOrMakeTab('Root.Newsletter', 'Newsletter');
			//PAGE LAYOUT
			$fields->addFieldToTab(
				"Root.Newsletter",
				new OptionsetField(
					$name = 'NewsletterAPI',
					$title = 'Newsletter API',
					$source = array(
						'constantcontact' => 'Constant Contact',
						'mailchimp' => 'Mail Chimp'
					),
					$value = 'constantcontact'
				)
			);
			
			/* MAILCHIMP */
			$fields->addFieldToTab('Root.Newsletter', HeaderField::create('Mailchimp'));
			/* API KEY*/
			$fields->addFieldToTab('Root.Newsletter', TextField::create('MailchimpApikey', 'API Key'));
			/**/
			$fields->addFieldToTab('Root.Newsletter', new CheckboxField('MailchimpCaptureFirstName', 'Capture First Name?'));
			/**/
			$fields->addFieldToTab('Root.Newsletter', new CheckboxField('MailchimpCaptureLastName', 'Capture Last Name?'));
			/**/
			if($this->owner->MailchimpApikey){
				require_once(MAILCHIMP_INCLUDES.'/MailChimp.class.php');
				$MailChimp = new MailChimp($this->owner->MailchimpApikey);
				$lists = $MailChimp->call('lists/list');
				if($lists){
					$tmp = array();
					foreach($lists["data"] as $list){
						$tmp[$list['id']] = $list['name'];
					}
					$fields->addFieldToTab('Root.Newsletter', new DropdownField('MailchimpListName', "Select list", $tmp));
				}
			}
			
			/* CONSTANT CONTACT */
			$fields->addFieldToTab('Root.Newsletter', HeaderField::create('Constant Contact'));
			/* API KEY*/
			$fields->addFieldToTab('Root.Newsletter', new TextField('ConstantContactApikey', 'API Key'));
			/**/
			$fields->addFieldToTab('Root.Newsletter', new TextField('ConstantContactUsername', 'Username'));
			/**/
			$fields->addFieldToTab('Root.Newsletter', new TextField('ConstantContactPassword', 'Password'));
			/**/
			if($this->owner->ConstantContactApikey){
				require_once(CONSTANTCONTACT_INCLUDES.'/class.cc.php');
				$ConstantContact = new cc($this->owner->ConstantContactUsername, $this->owner->ConstantContactPassword, $this->owner->ConstantContactApikey);
				//RETURNS ALL CONSTANT CONTACT LISTS (MINUS THE GENERIC LISTS)
				$lists = $ConstantContact->get_all_lists('lists', 3);
				if($lists){
					$tmp = array();
					foreach($lists as $k => $v){
						$tmp[$v['id']] = $v['Name'];
					}
					$fields->addFieldToTab('Root.Newsletter', new DropdownField('ConstantContactListName', "Select list", $tmp));
				}
			}
			/**/
			$htmlField = new HTMLEditorField('NewsletterSuccessText', 'Success/Thankyou Text');
			$htmlField->addExtraClass('stacked');
			$htmlField->setRows(5);
			$fields->addFieldToTab('Root.Newsletter', $htmlField);
			/**/
			$htmlField = new HTMLEditorField('NewsletterErrorText', 'Error Text');
			$htmlField->addExtraClass('stacked');
			$htmlField->setRows(5);
			$fields->addFieldToTab('Root.Newsletter', $htmlField);
			
        }
	}
?>

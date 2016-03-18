<?php
	/**/
	class NewsletterSettings extends DataExtension {
		/**/
		private static $db = array(
			'NewsletterAPI' => "Enum('constantcontact,mailchimp,none', 'mailchimp')",

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
		private static $defaults = array(
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
					$title = 'Select your newsletter API',
					$source = array(
						'constantcontact' => 'Constant Contact',
						'mailchimp' => 'Mail Chimp',
						'none' => 'No API'
					),
					$value = 'constantcontact'
				)
			);

			/* MAILCHIMP */
			$HeaderMailchimp = DisplayLogicWrapper::create(
				LiteralField::create('HeaderMailchimp', '<h3>Mailchimp</h3><p>Once you enter your API information and save, you will be able to select which lists you want the entries to be stored.</p>')
			)->displayIf("NewsletterAPI")->isEqualTo("mailchimp")->end();
			$fields->addFieldToTab('Root.Newsletter', $HeaderMailchimp);
				/* API KEY*/
				$MailchimpApikey = TextField::create('MailchimpApikey', 'API Key')
					->displayIf("NewsletterAPI")->isEqualTo("mailchimp")->end();
				$fields->addFieldToTab('Root.Newsletter', $MailchimpApikey);
				/**/
				$MailchimpCaptureFirstName = CheckboxField::create('MailchimpCaptureFirstName', 'Capture First Name?')
					->displayIf("NewsletterAPI")->isEqualTo("mailchimp")->end();
				$fields->addFieldToTab('Root.Newsletter', $MailchimpCaptureFirstName);
				/**/
				$MailchimpCaptureLastName = CheckboxField::create('MailchimpCaptureLastName', 'Capture Last Name?')
					->displayIf("NewsletterAPI")->isEqualTo("mailchimp")->end();
				$fields->addFieldToTab('Root.Newsletter', $MailchimpCaptureLastName);
				/**/
				if($this->owner->MailchimpApikey && $this->owner->NewsletterAPI == 'mailchimp'){
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
			$HeaderConstantContact = DisplayLogicWrapper::create(
				LiteralField::create('HeaderConstantContact', '<h3>Constant Contact</h3><p>Once you enter your API information and save, you will be able to select which lists you want the entries to be stored.</p>')
			)->displayIf("NewsletterAPI")->isEqualTo("constantcontact")->end();
			$fields->addFieldToTab('Root.Newsletter', $HeaderConstantContact);
				/* API KEY*/
				$ConstantContactApikey = TextField::create('ConstantContactApikey', 'API Key')
					->displayIf("NewsletterAPI")->isEqualTo("constantcontact")->end();
				$fields->addFieldToTab('Root.Newsletter', $ConstantContactApikey);
				/**/
				$ConstantContactUsername = TextField::create('ConstantContactUsername', 'Username')
					->displayIf("NewsletterAPI")->isEqualTo("constantcontact")->end();
				$fields->addFieldToTab('Root.Newsletter', $ConstantContactUsername);
				/**/
				$ConstantContactPassword = TextField::create('ConstantContactPassword', 'Password')
					->displayIf("NewsletterAPI")->isEqualTo("constantcontact")->end();
				$fields->addFieldToTab('Root.Newsletter', $ConstantContactPassword);
				/**/
				if($this->owner->ConstantContactApikey && $this->owner->NewsletterAPI == 'constantcontact'){
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

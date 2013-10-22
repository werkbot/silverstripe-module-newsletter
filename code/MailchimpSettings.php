<?php
	/**/
	class MailchimpSettings extends DataExtension {
		/**/
		public static $db = array( 
			'MailchimpApikey' => 'Text',
    		"MailchimpListName" => "Text",
    		"MailchimpCaptureFirstName" => "Boolean",
    		"MailchimpCaptureLastName" => "Boolean",
    		"MailchimpSuccessText" => "HTMLText",
    		"MailchimpErrorText" => "HTMLText",
		);
		/**/
		public static $defaults = array( 
    		"MailchimpCaptureFirstName" => "0",
    		"MailchimpCaptureLastName" => "0"
		);
		/**/
        public function updateCMSFields(FieldList $fields) {
			/**/
			$fields->findOrMakeTab('Root.MailChimp', 'MailChimp');
			/**/
			$fields->addFieldToTab('Root.MailChimp', new TextField('MailchimpApikey', 'API Key'));
			/**/
			if($this->owner->MailchimpApikey){
				$MailChimp = new MailChimp($this->owner->MailchimpApikey);
				$lists = $MailChimp->call('lists/list');
				if($lists){
					$tmp = array();
					foreach($lists["data"] as $list){
						$tmp[$list['id']] = $list['name'];
					}
					$fields->addFieldToTab('Root.MailChimp', new DropdownField('MailchimpListName', "Select list", $tmp));
				}
				/**/
				$fields->addFieldToTab('Root.MailChimp', new CheckboxField('MailchimpCaptureFirstName', 'Capture First Name?'));
				/**/
				$fields->addFieldToTab('Root.MailChimp', new CheckboxField('MailchimpCaptureLastName', 'Capture Last Name?'));
			}
			/**/
			$htmlField = new HTMLEditorField('MailchimpSuccessText', 'Success/Thankyou Text');
			$htmlField->addExtraClass('stacked');
			$htmlField->setRows(5);
			$fields->addFieldToTab('Root.MailChimp', $htmlField);
			/**/
			$htmlField = new HTMLEditorField('MailchimpErrorText', 'Error Text');
			$htmlField->addExtraClass('stacked');
			$htmlField->setRows(5);
			$fields->addFieldToTab('Root.MailChimp', $htmlField);
			
        }
	}
?>

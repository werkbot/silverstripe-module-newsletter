<?php
/**/
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
/**/
class NewsletterSettings extends DataExtension {
	/**/
	private static $db = [
		'NewsletterAPI' => "Enum('constantcontact,mailchimp,none', 'none')",
		"NewsletterSuccessText" => "HTMLText",
		"NewsletterErrorText" => "HTMLText",
		'NewsletterFormButtonText' => "Text",
	];
	/**/
  public function updateCMSFields(FieldList $fields) {
		/**/
		$fields->findOrMakeTab('Root.Newsletter', 'Newsletter');
		//PAGE LAYOUT
		$fields->addFieldToTab(
			"Root.Newsletter",
			OptionsetField::create(
				'NewsletterAPI',
				'Select your newsletter API',
				array(
					/*'constantcontact' => 'Constant Contact',
					'mailchimp' => 'Mail Chimp',*/
					'none' => 'No API'
				),
				'none'
			)
		);

		//BUTTON TEXT
		$fields->addFieldToTab("Root.Newsletter", TextField::create("NewsletterFormButtonText", "Button Text"));

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

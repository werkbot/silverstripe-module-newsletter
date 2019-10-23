<?php
/**/
use SilverStripe\Core\Environment;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
/**/
class NewsletterSettings extends DataExtension {
	/**/
	private static $db = [
		'NewsletterAPI' => "Enum('campaignmonitor,mailchimp,none', 'none')",
		"NewsletterSuccessText" => "HTMLText",
		"NewsletterErrorText" => "HTMLText",
		'NewsletterFormButtonText' => "Text",

    'CampaignMonitorListID' => "Text",
	];
	/**/
  public function updateCMSFields(FieldList $fields) {

		$fields->findOrMakeTab('Root.Newsletter', 'Newsletter');

		$fields->addFieldToTab(
			"Root.Newsletter",
			OptionsetField::create(
				'NewsletterAPI',
				'Select your newsletter API',
				array(
					'campaignmonitor' => 'Campaign Monitor',
					'none' => 'No API'
				),
				'none'
			)
		);

    // Campaign Monitor
    if(Environment::getEnv('CAMPAIGNMONITOR_API_KEY') && Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID')){
      $auth = array('api_key' => Environment::getEnv('CAMPAIGNMONITOR_API_KEY'));
      $wrap = new CS_REST_Clients(Environment::getEnv('CAMPAIGNMONITOR_CLIENT_ID'), $auth);
      $result = $wrap->get_lists();
      //echo "<pre>";print_r($result);die();
			$listarray = array();
      foreach($result->response as $list){
        $listarray[$list->ListID] = $list->Name;
      }
      $CampaignMonitorListID = DropdownField::create("CampaignMonitorListID", "Select a list", $listarray)
        ->displayIf("NewsletterAPI")->isEqualTo("campaignmonitor")->end();
  		$fields->addFieldToTab('Root.Newsletter', $CampaignMonitorListID);
    }

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
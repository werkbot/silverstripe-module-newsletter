<?php
/**/
use SilverStripe\Core\Environment;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use Ctct\ConstantContact;
/**/
class NewsletterSettings extends DataExtension {
	/**/
	private static $db = [
		'NewsletterAPI' => "Enum('campaignmonitor,mailchimp,constantcontact,none', 'none')",
		"NewsletterSuccessText" => "HTMLText",
		"NewsletterErrorText" => "HTMLText",

    'CampaignMonitorListID' => "Text",
    'ConstantContactListID' => "Text"
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
					'constantcontact' => 'Constant Contact',
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

    // Constant Contact
    if(Environment::getEnv('CONSTANTCONTACT_API_KEY') && Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN')){
      $cc = new ConstantContact(Environment::getEnv('CONSTANTCONTACT_API_KEY'));
      $lists = $cc->listService->getLists(Environment::getEnv('CONSTANT_CONTACT_ACCESS_TOKEN'));
	  $listarray = array();
      foreach($lists as $list){
        $listarray[$list->id] = $list->name;
      }
      $ConstantContactListID = DropdownField::create("ConstantContactListID", "Select a list", $listarray)
        ->displayIf("NewsletterAPI")->isEqualTo("constantcontact")->end();
  		$fields->addFieldToTab('Root.Newsletter', $ConstantContactListID);
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

<?php
/**/
class NewsletterAdmin extends ModelAdmin {
  /**/
  private static $managed_models = array(
    "NewsletterSubmission"
  );
  /**/
  private static $url_segment = 'newsletter-submissions';
  private static $menu_title = 'Newsletter';
  private static $menu_icon = '/newsletter-module/images/newsletter-icon.png';
  /**/
  public $showImportForm = array();
  /**/
  public function init(){
    parent::init();
  }
  /**/
  public function getEditForm($id=null, $fields=null) {
    $form = parent::getEditForm($id, $fields);

    $gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->addComponent(new GridFieldFilterHeader());
    $gridField->getConfig()->removeComponent($gridField->getConfig()->getComponentByType('GridFieldAddNewButton'));

    return $form;
  }
}

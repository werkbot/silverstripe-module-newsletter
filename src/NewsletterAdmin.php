<?php

namespace Werkbot\Newsletter;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;

/**
 * Newsletter Model Admin. Manages Newsletter Submissions.
 */
class NewsletterAdmin extends ModelAdmin
{

  private static $managed_models = [
    NewsletterSubmission::class,
  ];

  private static $url_segment = 'newsletter-submissions';
  private static $menu_title = 'Newsletter';
  private static $menu_icon_class = 'font-icon-block-email';

  public $showImportForm = [];

  /**
   * Main NewsletterAdmin edit form.
   *
   * @param int|null $id
   * @param \SilverStripe\Forms\FieldList $fields
   * @return \SilverStripe\Forms\Form A Form object with one tab per {@link \SilverStripe\Forms\GridField\GridField}
   */
  public function getEditForm($id = null, $fields = null)
  {
    $form = parent::getEditForm($id, $fields);
    $gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
    $gridField->getConfig()->removeComponentsByType(GridFieldAddNewButton::class);
    return $form;
  }
}

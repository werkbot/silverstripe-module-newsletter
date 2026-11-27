<?php

namespace Werkbot\Newsletter;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\CheckboxField;

/**
 * A page extension that adds the option to display/hide the newsletter sign-up form.
 */
class NewsletterPageExtender extends Extension
{

  private static $db = [
    "NewsletterShowHide" => "Boolean(1)",
  ];

  private static $defaults = [
    "NewsletterShowHide" => true,
  ];

  private static $casting = [
    'NewsletterShortcode' => 'HTMLText'
  ];

  public function onAfterPopulateDefaults()
  {
    $this->getOwner()->NewsletterShowHide = true;
    parent::onAfterPopulateDefaults();
  }

  public function updateSettingsFields(FieldList $fields)
  {
    $NewsletterTab = $fields->findOrMakeTab('Root.Newsletter');

    $NewsletterShowHide = FieldGroup::create(
      CheckboxField::create('NewsletterShowHide', 'Show the newsletter signup form on this page?')
    );

    $NewsletterTab->push(Tab::create('NewsletterTab', 'Newsletter Options', $NewsletterShowHide));
  }
}

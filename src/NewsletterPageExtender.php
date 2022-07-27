<?php

namespace Werkbot\Newsletter;

use SilverStripe\Forms\Tab;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CheckboxField;

/**
 * A page extension that adds the option to display/hide the newsletter sign-up form.
 */
class NewsletterPageExtender extends DataExtension
{

  private static $db = [
    "NewsletterShowHide" => "Boolean(1)",
  ];

  private static $defaults = [
    "NewsletterShowHide" => true,
  ];

  public function populateDefaults()
  {
    $this->owner->NewsletterShowHide = true;
    parent::populateDefaults();
  }

  public function updateSettingsFields(FieldList $fields)
  {
    $NewsletterTab = $fields->findOrMakeTab('Root.Newsletter');

    $NewsletterShowHide = FieldGroup::create(
      CheckboxField::create('NewsletterShowHide', 'Show the newsletter signup form on this page?')
    );

    $NewsletterTab->push(new Tab(
      'NewsletterTab',
      'Newsletter Options',
      $NewsletterShowHide
    ));
  }
}

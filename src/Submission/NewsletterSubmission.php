<?php

namespace Werkbot\Newsletter;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadOnlyField;
use SilverStripe\ORM\DataObject;

/**
 * A DataObject to store subscriber information.
 */
class NewsletterSubmission extends DataObject
{
  private static $singular_name = 'Newsletter Submission';
  private static $plural_name = 'Newsletter Submissions';
  private static $table_name = 'NewsletterSubmission';
  private static $default_sort = "Created DESC";

  private static $db = [
    "Email" => "Text",
    "FirstName" => "Text",
    "LastName" => "Text",
  ];

  private static $summary_fields = [
    "Created" => "Created Date",
    "Email" => "Email Address",
  ];

  private static $searchable_fields = [
    "Created",
    "Email",
  ];

  public function getCMSFields()
  {
    $fields = new FieldList(
      ReadOnlyField::create("Created", "Created Date"),
      ReadOnlyField::create("Email", "Email"),
      ReadOnlyField::create("FirstName", "First Name"),
      ReadOnlyField::create("LastName", "Last Name")
    );

    $this->extend('updateCMSFields', $fields);

    return $fields;
  }
}

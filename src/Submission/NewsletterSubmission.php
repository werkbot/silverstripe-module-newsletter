<?php
/**/
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadOnlyField;
use SilverStripe\ORM\DataObject;
/**/
class NewsletterSubmission extends DataObject {
  /**/
  private static $singular_name = 'Newsletter Submission';
  private static $plural_name = 'Newsletter Submissions';
  /**/
  private static $default_sort = "Created DESC";
  /**/
  private static $db = [
    "Email" => 'Text',
  ];
  /**/
  private static $summary_fields = [
    "Created" => "Created Date",
    "Email" => "Email Address"
  ];
  /**/
  private static $searchable_fields = [
    "Created",
    "Email"
  ];
  /**/
  public function getCMSFields() {
    //
    $fields = new FieldList(
      ReadOnlyField::create("Created", "Created Date"),
      ReadOnlyField::create("Email", "Email")
    );
    //
    $this->extend('updateCMSFields', $fields);

    return $fields;
  }
}

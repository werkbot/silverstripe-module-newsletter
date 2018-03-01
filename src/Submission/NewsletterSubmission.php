<?php
/**/
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadOnlyField;
/**/
class NewsletterSubmission extends DataObject {
  /**/
  static $singular_name = 'Newsletter Submission';
  static $plural_name = 'Newsletter Submissions';
  /**/
  private static $default_sort = "Created DESC";
  /**/
  private static $db = [
    "Email" => 'Text',
  ];
  /**/
  public static $summary_fields = [
    "Created" => "Created Date",
    "Email" => "Email Address"
  ];
  /**/
  public static $searchable_fields = [
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

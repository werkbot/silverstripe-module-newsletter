<?php
/**/
class NewsletterSubmission extends DataObject {
  /**/
  static $singular_name = 'Newsletter Submission';
  static $plural_name = 'Newsletter Submissions';
  /**/
  private static $db = array(
    "Email" => 'Text',
    "FirstName" => 'Text',
    "LastName" => 'Text'
  );
  /**/
  private static $default_sort = "Created DESC";
  /**/
  public static $summary_fields = array(
    "Created" => "Created Date",
    "Email" => "Email Address",
    "FirstName" => "First Name",
    "LastName" => "Last Name"
  );
  /**/
  public static $searchable_fields = array(
    "Created",
    "Email",
    "FirstName",
    "LastName"
  );
  /**/
  public function getCMSFields() {
    //
    $fields = new FieldList(
      ReadOnlyField::create("Created", "Created Date"),
      ReadOnlyField::create("Email", "Email"),
      ReadOnlyField::create("FirstName", "First Name"),
      ReadOnlyField::create("LastName", "Last Name")
    );
    //
    $this->extend('updateCMSFields', $fields);

    return $fields;
  }
  /**/
	function canView($member = false) {
		return true;
	 }
	 /**/
	 function canEdit($member = false) {
		return true;
	 }
	 /**/
	 function canDelete($member = false) {
	   return true;
	 }
	 /**/
	 function canCreate($member = false) {
	    return true;
	 }
}

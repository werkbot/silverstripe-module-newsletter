<?php

namespace Werkbot\Newsletter;

use SilverStripe\Dev\BuildTask;

/**
 * A schedueled task that removes old newsletter submissions from the database.
 */
class RemoveSubmissions extends BuildTask
{
  protected $title = "Remove Submissions";
  protected $description = "Removes old newsletter submissions from the database.";
  protected $enabled = true;
  private static $remove_submission_cuttoff = "-30 days";

  public function run($request)
  {
    $CutoffDate = date('Y-m-d H:i:s', strtotime($this->config()->remove_submission_cuttoff));
    $NewsletterSubmissions = NewsletterSubmission::get()->filter(array("Created:LessThan" => $CutoffDate));
    $Total = $NewsletterSubmissions->Count();
    $NewsletterSubmissions->removeAll();
    echo $Total . " newsletter submissions removed";
  }
}

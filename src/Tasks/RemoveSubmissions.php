<?php
/**/
namespace Werkbot\Newsletter;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Config\Config;
/**/
class RemoveSubmissions extends BuildTask
{
  /**/
  protected $title = "Remove Submissions";
  protected $description = "Removes old newsletter submissions from the database.";
  protected $enabled = true;
  /**/
  public function run($request)
  {
    $CutoffDate = date('Y-m-d H:i:s', strtotime(Config::inst()->get('Werkbot\Newsletter', 'remove_submittion_cuttoff')));
    $NewsletterSubmissions = NewsletterSubmission::get()->filter(array("Created:LessThan"=>$CutoffDate));
    $Total = $NewsletterSubmissions->Count();
    $NewsletterSubmissions->removeAll();
    echo $Total." newsletter submissions removed";
  }
}

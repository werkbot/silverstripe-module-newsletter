<?php

namespace Werkbot\Newsletter;

use Symfony\Component\Console\Input\InputInterface;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use SilverStripe\Dev\BuildTask;

/**
 * A schedueled task that removes old newsletter submissions from the database.
 */
class RemoveSubmissions extends BuildTask
{
  protected string $title = "Remove Submissions";
  protected static string $description = "Removes old newsletter submissions from the database.";
  /**
   * @config
   */
  private static $is_enabled = true;
  private static $remove_submission_cuttoff = "-30 days";

  protected function execute(InputInterface $input, PolyOutput $output): int
  {
      $CutoffDate = date('Y-m-d H:i:s', strtotime($this->config()->remove_submission_cuttoff));
      $NewsletterSubmissions = NewsletterSubmission::get()->filter(array("Created:LessThan" => $CutoffDate));
      $Total = $NewsletterSubmissions->Count();
      $NewsletterSubmissions->removeAll();
      echo $Total . " newsletter submissions removed";
      return Command::SUCCESS;
  }
}

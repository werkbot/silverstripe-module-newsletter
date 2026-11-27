<?php

namespace Werkbot\Newsletter;

use Symfony\Component\Console\Input\InputInterface;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Page;
use SilverStripe\Dev\BuildTask;

/**
 * A build task to enable the newsletter form on all pages.
 */
class EnableAllNewsletterForms extends BuildTask
{
    protected string $title = 'Enable All Newsletter Forms';
    protected static string $description = 'Enables the newsletter form for all pages.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        foreach (Page::get() as $page) {
            $page->NewsletterShowHide = true;
            $page->write();
            if ($page->isPublished()) {
                $page->publishRecursive();
            }
        }
        $output->writeln('All pages set to show newsletter form.');
        return Command::SUCCESS;
    }
}

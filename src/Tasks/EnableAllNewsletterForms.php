<?php

namespace Werkbot\Newsletter;

use Page;
use SilverStripe\Dev\BuildTask;

/**
 * A build task to enable the newsletter form on all pages.
 */
class EnableAllNewsletterForms extends BuildTask
{
    protected $title = 'Enable All Newsletter Forms';
    protected $description = 'Enables the newsletter form for all pages.';

    public function run($request)
    {
        foreach (Page::get() as $page) {
            $page->NewsletterShowHide = true;
            $page->write();
            if ($page->isPublished()) {
                $page->publishRecursive();
            }
        }
        echo 'All pages set to show newsletter form.';
    }
}

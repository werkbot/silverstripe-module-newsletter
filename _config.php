<?php

namespace Werkbot\Newsletter;

use Page;
use PageController;
use SilverStripe\SiteConfig\SiteConfig;

SiteConfig::add_extension(NewsletterSettings::class);
Page::add_extension(NewsletterPageExtender::class);
PageController::add_extension(NewsletterPageControllerExtender::class);

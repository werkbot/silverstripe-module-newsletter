<?php

namespace Werkbot\Newsletter;

use Page;
use PageController;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Parsers\ShortcodeParser;

SiteConfig::add_extension(NewsletterSettings::class);
Page::add_extension(NewsletterPageExtender::class);
PageController::add_extension(NewsletterPageControllerExtender::class);

ShortcodeParser::get('default')->register('newsletterform', [NewsletterShortcodeProvider::class, 'handle_shortcode']);

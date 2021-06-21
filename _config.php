<?php
  /**/
  use SilverStripe\SiteConfig\SiteConfig;
  /**/
	SiteConfig::add_extension('NewsletterSettings');
	Page::add_extension('NewsletterPageExtender');
	PageController::add_extension('NewsletterPageControllerExtender');
?>

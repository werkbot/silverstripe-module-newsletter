<?php
	define('NEWSLETTER_PATH',    realpath(dirname(__FILE__)));
	define('MAILCHIMP_INCLUDES', '../vendor/drewm/mailchimp-api/');
	define('CONSTANTCONTACT_INCLUDES', NEWSLETTER_PATH.'/constantcontact-api');
	//
	SiteConfig::add_extension('NewsletterSettings');
	//THIS WILL ADD OUR FUNCTIONALITY TO ALL PAGES IN THE SITE
	Page_Controller::add_extension('NewsletterPageExtender');
?>

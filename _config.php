<?php
	define('MAILCHIMP_PATH',    realpath(dirname(__FILE__)));
	define('MAILCHIMP_INCLUDES', MAILCHIMP_PATH.'/mailchimp-api');
	require_once(MAILCHIMP_INCLUDES.'/MailChimp.class.php');
	//
	SiteConfig::add_extension('MailchimpSettings');
	//THIS WILL ADD OUR FUNCTIONALITY TO ALL PAGES IN THE SITE 
	Page_Controller::add_extension('MailchimpPageExtender');
?>
# Newsletter Module
Silverstripe module to capture user emails.
## Requirements
- Silverstripe ^4.0

## Setup
Update composer.json:

	"repositories": [
		...
      {
	        "type": "vcs",
	        "url": "https://github.com/werkbot/silverstripe-module-newsletter.git"
	  }
	],
	"require": [
		...
		"werkbot/newsletter-module": "*"
	]

Run `composer update`

Run `/dev/build` on your site

You can use `$NewsletterForm` in your templates:

To use the correct styles, create a sass/components/newsletter folder, and copy _newsletter.scss from vendor/werkbot/newsletter-module/sass here

Import the file in common.scss `@import 'components/newsletter/newsletter';`

Run `grunt build` to compile styles

## Usage
- The newsletter submissions will appear in their own tab
- Newsletter settings exist in the settings tab
- The admin can add success and error messages to display when the form is submitted. You must include this message in your template:

      <% if $NewsletterMessage %>
        <div class="newsletter-message $MessageType">
          <div class="container">
            <div class="space">
              $NewsletterMessage.RAW
            </div>
          </div>
        </div>
      <% end_if %>

## Remove Submissions Task
The remove submission task will remove newsletter submissions older then the default of 30 days. 
This value can be altered with the following:
```
---
Name: Newsletter
---
Werkbot\Newsletter\RemoveSubmissions:
  remove_submission_cuttoff: -130 days
```

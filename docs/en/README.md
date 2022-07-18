## Usage

### Viewing Newsletter Subscriptions
- The newsletter submissions will appear in their own tab, under `/admin/newsletter-submissions`.

### Showing Newsletter Subscription Message
- Newsletter settings exist in the settings tab.
- The admin can add success and error messages to display when the form is submitted. You must include this message in your template:
```
<% if $NewsletterMessage %>
  <div class="newsletter-message $MessageType">
    <div class="container">
      <div class="space">
        $NewsletterMessage.RAW
      </div>
    </div>
  </div>
<% end_if %>
```

### Remove Submissions Task
The remove submission task will remove newsletter submissions older then the default of 30 days. 
This value can be altered with the following:
```
---
Name: Newsletter
---
Werkbot\Newsletter\RemoveSubmissions:
  remove_submission_cuttoff: -130 days
```

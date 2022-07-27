## Usage

### Viewing Newsletter Subscriptions
- The newsletter submissions will appear in their own tab, under `/admin/newsletter-submissions`.

### Showing Newsletter Subscription Message
- Newsletter settings exist in the settings tab: `/admin/settings`.
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

### Integrations
Newsletter subscribers can automatically be added to a third party mailing service. Your newsletter subscription form can integrate with any one of the following services:
- Campaign Monitor
- Mailchimp
- Constant Contact
- Active Campaign
- Redtail CRM

**To enable an integration:**
- Enter the environment variables for your desired integration. See the [.env.example](../../.env.example) file.
- In the newsletter settings (`/admin/settings`), select the desired newsletter API.
- Select a contact list to insert new subscribers. If no list selection appears, check your environment variables. Note that there are currently no list options for Redtail CRM.

# Infomaniak Newsletter Field

## Overview
Infomaniak Newsletter Field is a Drupal module that provides a custom field type for newsletter signup forms. This module integrates with the Infomaniak Newsletter API to allow users to subscribe to mailing lists directly from your Drupal site.

## Features
- Custom field type for newsletter signups
- AJAX form submission to prevent page reloading
- Email validation
- Double opt-in with validation emails
- Integration with Infomaniak Newsletter API v1
- Storage of subscription status in the database
- Field formatter for displaying the signup form
- Field widget for configuring the field in the admin interface

## Requirements
- Drupal 10 or 11
- PHP 8.1 or higher
- Infomaniak Newsletter account with API credentials

## Installation
1. Download the module and place it in your modules directory (typically `/modules/contrib/` or `/modules/custom/`).
2. Enable the module via the Drupal admin interface or using Drush:
   ```
   drush en infomaniak_newsletter_field
   ```
3. Configure the API credentials in the module settings.

## Configuration
1. Go to Administration > Configuration > Web services > Infomaniak Newsletter Settings
2. Enter your Infomaniak Newsletter API credentials:
   - Newsletter Base URL
   - Newsletter Token
   - Newsletter Domain

## Usage

### Adding a Newsletter Signup Field
1. Go to Structure > Content Types > [Your content type] > Manage fields
2. Add a new field of type "Newsletter Signup"
3. Configure the field settings as desired

### Field Configuration
When configuring the Newsletter Signup field, you can set:
- The mailing list ID (pulled from your Infomaniak account)
- The submit button text

### Theming
The module provides two templates for customization:
- `newsletter-signup-message.html.twig`: Displays success/error messages
- `newsletter-signup-validation.html.twig`: Displays validation success message

You can override these templates in your theme by copying them to your theme's templates directory.

## How It Works
1. A user submits their email through the newsletter signup form
2. The module validates the email format
3. The subscription is stored in the database with a validation status of 0 (unvalidated)
4. A validation email is sent to the user
5. When the user clicks the validation link, the subscription is validated and sent to Infomaniak
6. The validation status is updated to 1 (validated)

## API Integration
The module provides an interface (`NewsletterApiServiceInterface`) and implementations for interacting with the Infomaniak Newsletter API:
- `InfomaniakV2ApiService`: Main implementation for production use
- `MockupApiService`: Mock implementation for testing

## Database Schema
The module creates a table `infomaniak_newsletter_field_subscriptions` with the following structure:
- `email`: Email address of the subscriber (varchar, 254, primary key)
- `mailinglist_id`: ID of the mailing list (varchar, 128, primary key)
- `timestamp`: Unix timestamp of the subscription (int)
- `validation_status`: Boolean indicating whether the subscription has been validated (tinyint/boolean)

## Troubleshooting
- Check the Drupal logs for any errors related to the API communication
- Verify your API credentials are correct
- Ensure the mailing list ID is valid and active

## Credits
Developed for integration with Infomaniak Newsletter services.

## License
This module is licensed under the GNU General Public License v2.0 or later.

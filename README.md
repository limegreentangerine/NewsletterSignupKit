# NewsletterSignupKit

[![Code Standards](https://github.com/limegreentangerine/NewsletterSignupKit/actions/workflows/CodeStandards.yml/badge.svg)](https://github.com/limegreentangerine/NewsletterSignupKit/actions/workflows/CodeStandards.yml)
[![System Tests](https://github.com/limegreentangerine/NewsletterSignupKit/actions/workflows/SystemTests.yml/badge.svg)](https://github.com/limegreentangerine/NewsletterSignupKit/actions/workflows/SystemTests.yml)

A Concrete CMS package that integrates **Mailchimp** and **Campaign Monitor**
for newsletter signups. It provides subscription form blocks, dashboard pages
for settings and mailing lists, list attributes for Express, and scheduled
tasks that sync lists and retry failed signups.

- Package handle: `newsletter_signup_kit`
- Composer name: `limegreentangerine/newsletter_signup_kit`
- Current version: see [CHANGELOG.md](CHANGELOG.md)

## Features

- **Subscription form blocks**: _Mailchimp Subscription Form_ and _Campaign
  Monitor Subscription Form_. Each block is tied to one mailing list chosen in
  the block editor, and supports plain and AJAX submissions.
- **Resilient signups**: if the provider API rejects a signup or errors, the
  subscriber is stored locally and retried by a scheduled task.
- **Input handling**: form input is stripped of markup and control characters,
  length-capped, and the email address is validated. The list ID is always taken
  from the block configuration, never from the submitted form.
- **List attributes**: _Mailchimp List_ and _Campaign Monitor List_ attribute
  types for Express entities.
- **Dashboard pages** under **Newsletter Signup**:
    - **Settings**: shows which provider credentials are populated (secrets are
      masked). Values are read-only here and come from `.env`.
    - **Mailing Lists**: the imported lists, with bulk actions.
- **Uninstall confirmation**: uninstalling requires ticking **Confirm
  Uninstall**.

## Requirements

- Concrete CMS 9.5.0 or later.
- PHP 8.4 or later.
- The [`class_kit`](https://packages.limegreentangerine.net) package installed
  in Concrete (declared as a package dependency).
- A Mailchimp and/or Campaign Monitor account with an API key.

Composer dependencies: `limegreentangerine/class_kit`, `mailchimp/marketing`
and `campaignmonitor/createsend-php`.

## Installation

1. Place the package in `packages/newsletter_signup_kit` (or require it via
   Composer from `https://packages.limegreentangerine.net`) and run
   `composer install` in the package directory.
2. Install `class_kit`, then install **NewsletterSignupKit** from
   **Dashboard > Extend Concrete**.

Installing registers the block types, the Express attribute types, the dashboard
pages and the scheduled tasks.

## Configuration

Credentials are read from environment variables, either set in the process
environment or in a `.env` file at the package root (the process environment
takes precedence, so CLI task runs work either way).

| Provider         | Variable                  | Required | Notes                                                   |
| ---------------- | ------------------------- | -------- | ------------------------------------------------------- |
| Mailchimp        | `MAILCHIMP_API_KEY`       | Yes      | Secret.                                                 |
| Mailchimp        | `MAILCHIMP_SERVER_PREFIX` | Yes      | The data-centre prefix of your key, for example `us21`. |
| Campaign Monitor | `CAMPAIGNMONITOR_API_KEY` | Yes      | Secret.                                                 |

```dotenv
MAILCHIMP_API_KEY=your-key
MAILCHIMP_SERVER_PREFIX=us21
CAMPAIGNMONITOR_API_KEY=your-key
```

Check **Dashboard > Newsletter Signup > Settings** to confirm each value is
populated. A provider that is not fully configured is skipped by the tasks.

## Usage

1. Configure credentials and run the list-import task (see below) so the mailing
   lists appear under **Newsletter Signup > Mailing Lists**.
2. Add a **Mailchimp Subscription Form** or **Campaign Monitor Subscription
   Form** block to a page and choose its mailing list.
3. On a successful non-AJAX signup the visitor is redirected to `/thank_you`.
   AJAX submissions receive a JSON response of `{"success": true}` or
   `{"success": false, "message": "..."}`.

## Scheduled tasks

Tasks are grouped in the **Newsletter Signup** task set under **Dashboard >
System & Settings > Automation**, and can be scheduled or run from the CLI.

| Task handle                        | Purpose                                                    |
| ---------------------------------- | ---------------------------------------------------------- |
| `get_mailchimp_lists`              | Import Mailchimp lists.                                    |
| `get_campaign_monitor_lists`       | Import Campaign Monitor lists.                             |
| `add_mailchimp_subscribers`        | Send queued (unprocessed) subscribers to Mailchimp.        |
| `add_campaign_monitor_subscribers` | Send queued (unprocessed) subscribers to Campaign Monitor. |

Queued subscribers are marked processed only once the provider accepts them, so
failures are retried on the next run. Provider activity and errors are written
to per-provider logs.

```bash
./concrete/bin/concrete c5:task get_mailchimp_lists
```

## Development

```bash
composer install
```

Composer runs `npm install` afterwards. Installing private dependencies needs
access to `https://packages.limegreentangerine.net`; configure Composer to send
the Cloudflare Access service-token headers for that host
(`CF-ACCESS-CLIENT-ID` and `CF-ACCESS-CLIENT-SECRET`) and keep the credentials
out of the repository.

| Command                                             | Purpose                                                                                                            |
| --------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `composer test`                                     | Run PHPUnit tests.                                                                                                 |
| `composer test-coverage`                            | Run PHPUnit and print a coverage report.                                                                           |
| `composer typecheck`                                | Run PHPStan (`phpstan.neon`). Findings are displayed, but the command exits successfully even if errors are found. |
| `composer format:check` / `composer format`         | Check or apply PHP and JavaScript formatting.                                                                      |
| `composer format:php:check` / `composer format:php` | Check or format PHP files with PHP-CS-Fixer.                                                                       |
| `composer format:js:check` / `composer format:js`   | Check or format files with Prettier.                                                                               |

If you add package directories, update `paths` in `phpstan.neon` and the
directories in `.php-cs-fixer.dist.php`.

### Continuous integration

- **CodeStandards** runs formatting checks and PHPStan on pushes and pull
  requests.
- **SystemTests** runs PHPUnit on pull requests.

Both authenticate to the private registry using the repository secrets
`CF_ACCESS_CLIENT_ID` and `CF_ACCESS_CLIENT_SECRET`.

### Code layout

| Path                                      | Contents                                                        |
| ----------------------------------------- | --------------------------------------------------------------- |
| `controller.php`                          | Package controller: install, tasks, attributes, dashboard pages |
| `blocks/`                                 | Subscription form block types                                   |
| `attributes/`                             | Mailchimp and Campaign Monitor list attribute types             |
| `single_pages/`, `controllers/`           | Dashboard pages and their controllers                           |
| `elements/dashboard/`                     | Dashboard elements, including the uninstall confirmation        |
| `src/Api`, `src/Config`                   | Provider API clients and environment-based configuration        |
| `src/Command`                             | Task commands and handlers                                      |
| `src/Entity`, `src/Express`, `src/Search` | Doctrine entities, Express controllers and list search          |

## Further reading

- https://github.com/campaignmonitor/createsend-php
- https://github.com/mailchimp/mailchimp-marketing-php

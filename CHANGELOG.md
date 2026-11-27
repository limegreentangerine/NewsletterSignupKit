# Changelog

Notable changes to this project are documented here.

## 1.0.4

### Added

- Subscription blocks: a "Thank You Page" picker. When AJAX submission is off,
  a page must be chosen and successful sign-ups are redirected to it. Blocks
  saved before this version keep redirecting to `/thank_you` until edited.

### Changed

- The edit dialog shows the thank you page picker only when AJAX is off, and
  the success message only when AJAX is on.
- Non-AJAX submission errors are now shown on the page in the same place as
  AJAX errors, and the visitor is not redirected.

### Fixed

- Campaign Monitor sign-ups were always treated as failures (and queued for
  the batch job) because success was detected incorrectly.
- Mailchimp API errors (e.g. invalid email) now show Mailchimp's message
  instead of an error page on non-AJAX forms.
- Subscribers the provider rejects outright (HTTP 400) are no longer added
  to the batch queue.
- AJAX error messages are inserted as text rather than HTML.
- An AJAX success message containing an apostrophe or line break no longer
  breaks the form's script.

## 1.0.2

### Added

- Dashboard uninstall confirmation: the package now shows an "Are you sure?"
  prompt with a required confirmation checkbox before it can be uninstalled.

### Changed

- Bumped the package version to 1.0.2.

## 0.0.0

### Added

- Separate GitHub Actions workflows for code standards and system tests.

### Changed

- Code standards checks run on pushes and pull requests; system tests run on
  pull requests.
- Updated Composer dependencies and development tooling for the package
  template.

## 2026-09-25

### Added

- PHPStan static analysis configuration and a Composer command for type checks.
- Cloudflare Access authentication for installing dependencies from the private
  Composer registry.

### Changed

- Updated the template to use the published `limegreentangerine/class_kit`
  Composer package.
- Refined the GitHub Actions workflows and development dependencies.

## 2026-09-15

### Added

- Initial Concrete CMS Composer package template, including Composer
  autoloading, PHPUnit test setup, and a GitHub Actions test workflow.

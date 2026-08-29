# Marketplace for Azuriom

Marketplace is a community resource platform for Azuriom. It allows members to publish free or paid resources, distribute downloadable files or external links, release version updates, and interact through comments and ratings.

The plugin includes category access controls, content moderation, reporting tools, granular staff permissions, notifications, and defensive file-upload policies.

## Requirements

- Azuriom extension API `1.2.0`
- PHP `8.2` or newer
- PHP GD and Fileinfo extensions (EXIF is recommended for JPEG orientation)
- Laravel `12` through a compatible Azuriom installation

## Features

### Resource publishing

- Publish downloadable files or link to an external website.
- Use UUID-based resource URLs, preventing collisions between resources with the same name.
- Add a title, version, summary, rich description, banner, category, delivery method, and coin price.
- Rich descriptions powered by TinyMCE and sanitized on the server before storage.
- Direct JPG, PNG, and WebP uploads from TinyMCE with private storage, server-side re-encoding, access control, and orphan cleanup.
- Banner images displayed in resource cards and on the resource page.
- Separate resource information and version-history tabs.
- Publish new versions with a changelog without editing the resource description.
- View up to four recent resources from the same author.
- Browse a member's own resources regardless of their moderation status.

### Discovery and categories

- Administrator-managed categories with icons, descriptions, ordering, and enabled status.
- Administrator-managed global or category-specific tags with descriptions, colors, ordering, and enabled status.
- Optional category access restrictions by Azuriom role.
- Independent role restrictions for publishing in categories and assigning tags.
- Optional many-to-many tag assignment when creating or editing resources.
- Resource counters for each category.
- Administrative resource lists per category.
- Sorting by recently updated, download count, or highest rating.
- Permission-controlled resource pinning that takes precedence over every listing sort.
- Consistent public and administrative breadcrumb navigation across Marketplace pages.

### Free and paid resources

- Free resources are immediately available to eligible users.
- Administrators can allow guest downloads for free resources or require users to sign in first.
- Paid resources are unlocked with the Azuriom site currency.
- Purchases transfer coins from the buyer to the resource author inside a database transaction.
- Purchasing a resource unlocks its download, comments, and ratings.
- Purchased resources are collected in a personal library and receive new-version notifications.
- Users can follow free resources to receive the same update notifications.
- Paid-resource authors can generate hashed, limited-use gift codes containing one or more of their resources.
- Gift-code redemptions grant zero-price purchase records without transferring coins.
- Paid-resource comments and ratings require a purchase record; ownership or staff download bypasses do not grant interaction access.
- Authorized staff can download paid resources without purchasing them.

### Downloads and external links

- Downloadable resources are stored on Laravel's private local disk.
- External resources display a confirmation page before the user leaves the site.
- Downloads are counted after access checks succeed.
- File size and allowed extensions are configurable from the administration panel.
- The default upload whitelist is `.zip`, `.rar`, `.7z`, and `.jar`.
- Files without an extension or outside the configured whitelist are rejected.
- Dangerous extensions such as PHP, JavaScript, scripts, executables, server configuration files, and templates are permanently blocked and cannot be added to the whitelist.

### Comments, ratings, and reports

- Unlocked users can comment on and rate an active resource.
- Ratings range from one to five stars and can be updated by the same user.
- Resource authors receive an Azuriom notification when another user comments.
- Users can report resources or comments and provide a reason in a confirmation modal.
- Duplicate reports from the same user for the same content are prevented.
- Administrators can review resource and comment reports from a dedicated panel page.
- Users can be restricted from comments, new resources, resource edits, or version updates for a fixed period or indefinitely.

### Moderation

- Optional approval queue for newly submitted or edited resources.
- A bypass permission allows trusted roles to publish without entering the queue.
- Dedicated administration page for pending resources, including publication date.
- Approval and rejection notifications are sent to the resource author.
- Rejections require a reason, which is included in the notification.
- Moderation tools are available directly from the resource page through a compact dropdown.
- Destructive and state-changing moderation actions require modal confirmation.
- Resources can be paused, archived, edited, permanently deleted, approved, or rejected.
- Authorized staff can review archived resources from the administration panel and restore them to their previous status.
- Paused resources cannot be downloaded, purchased, commented on, or rated.
- Archived resources are hidden through a global scope while their data remains stored.
- Moderators can delete individual comments, delete every comment by a user, and reset a resource's ratings.
- Moderators with resource-viewing tools can inspect published, pending, and rejected resources.

### Administration dashboard

The Marketplace administration menu contains dedicated pages for:

- Categories
- Tags
- Pending resources
- Archived resources (requires the archive permission)
- Community reports
- User restrictions
- Settings and marketplace statistics

The settings dashboard shows published resources, resources awaiting approval, and total coins spent on resource purchases. Administrators can also:

- Enable or disable publication moderation.
- Pause new resource submissions without preventing updates to existing resources.
- Pause new comments globally.
- Require an account for free-resource downloads or allow guests to download them.
- Configure the maximum resource file size.
- Configure the resource file extension whitelist.
- Configure TinyMCE image size and per-resource image limits.
- Apply, review, and lift per-user action restrictions with an optional expiration date and internal reason.

## Permissions

| Permission | Description |
| --- | --- |
| `marketplace.admin` | Access the Marketplace administration pages. |
| `marketplace.publish` | Publish new resources in the Marketplace. |
| `marketplace.moderate` | View moderation states and approve or reject pending resources. |
| `marketplace.bypass-moderation` | Publish resources without entering the moderation queue. |
| `marketplace.archive` | Archive resources and restore archived resources. |
| `marketplace.pause` | Pause and resume resources. |
| `marketplace.edit` | Edit any resource. |
| `marketplace.delete` | Permanently delete any resource. |
| `marketplace.delete-comments` | Delete comments and all comments submitted by a selected user. |
| `marketplace.reset-ratings` | Reset all ratings for a resource. |
| `marketplace.download-paid` | Download paid resources without purchasing them. |

Category restrictions are configured separately by selecting the Azuriom roles allowed to access each premium category.

## Installation

1. Place this repository in the Azuriom installation at `plugins/marketplace`.
2. From the Azuriom root directory, enable the plugin:

   ```bash
   php artisan plugin:enable marketplace
   ```

3. Assign the desired Marketplace permissions to staff roles.
4. Open **Administration > Marketplace > Settings** and review moderation, submission, comment, file-size, and extension-whitelist settings.
5. Create at least one enabled category under **Administration > Marketplace > Categories**.

The plugin registers and runs its database migration through Azuriom's plugin lifecycle.

## Development database reset

Marketplace keeps each table in its own migration file under `database/migrations`. The filenames are ordered so referenced tables are created before their dependants and rolled back in reverse order.

During development, if these migrations have already run and the schema files were modified, roll back only the Marketplace migrations and run them again from the Azuriom root:

```bash
php artisan migrate:rollback --path=plugins/marketplace/database/migrations
php artisan migrate --path=plugins/marketplace/database/migrations
```

Do not use this reset workflow on a production installation containing Marketplace data without first creating and verifying a database backup.

## Security notes

- Resource files and banners are stored on the private `local` filesystem disk and served through authorized controller actions.
- Resource descriptions are sanitized with an explicit HTML element, attribute, and URL-protocol allowlist.
- Source-code edits are filtered by TinyMCE and re-sanitized on the server, including malformed markup, active-content elements, event attributes, unsafe targets, and obfuscated URL protocols.
- External destinations must use HTTP or HTTPS and require an explicit confirmation step.
- File extension checks are enforced server-side for resource creation, editing, and version updates; the browser file filter is only an additional usability aid.
- The permanent dangerous-extension denylist is enforced independently of the saved administrator whitelist.
- Paid unlocks use database transactions and row locks to reduce double-purchase and balance-race risks.
- User restrictions are enforced by route middleware before rate-limit and CAPTCHA validation.

## Localization

Marketplace currently includes:

- English (`en`)
- Spanish (`es_ES`)

## Project structure

```text
marketplace/
├── database/migrations/   # Ordered Marketplace schema migrations
├── resources/lang/        # English and Spanish translations
├── resources/views/       # Public and administration Blade views
├── routes/                # Public and administration routes
├── src/Controllers/       # Public, moderation, and admin controllers
├── src/Models/            # Marketplace Eloquent models
├── src/Requests/          # Form request validation
├── src/Rules/             # Custom upload validation rules
├── src/Support/           # HTML sanitization and file policies
├── composer.json
└── plugin.json
```

## Authors

- Zibuu
- Kissadere

Project website: [zibuu.net](https://zibuu.net/)

## License

This project is proprietary. No permission to copy, redistribute, modify, or sublicense it is granted unless the authors provide written authorization.

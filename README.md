# Marketplace for Azuriom

Marketplace is a community resource platform for Azuriom. It allows members to publish free or paid resources, distribute downloadable files or external links, release version updates, and interact through comments and ratings.

The plugin includes category access controls, content moderation, reporting tools, granular staff permissions, notifications, and defensive file-upload policies.

## Requirements

- Azuriom extension API `1.2.0`
- PHP `8.2` or newer
- Laravel `12` through a compatible Azuriom installation

## Features

### Resource publishing

- Publish downloadable files or link to an external website.
- Use UUID-based resource URLs, preventing collisions between resources with the same name.
- Add a title, version, summary, rich description, banner, category, delivery method, and coin price.
- Rich descriptions powered by TinyMCE and sanitized on the server before storage.
- Banner images displayed in resource cards and on the resource page.
- Separate resource information and version-history tabs.
- Publish new versions with a changelog without editing the resource description.
- View up to four recent resources from the same author.
- Browse a member's own resources regardless of their moderation status.

### Discovery and categories

- Administrator-managed categories with icons, descriptions, ordering, and enabled status.
- Optional category access restrictions by Azuriom role.
- Resource counters for each category.
- Administrative resource lists per category.
- Sorting by recently updated, download count, or highest rating.

### Free and paid resources

- Free resources are immediately available to eligible users.
- Paid resources are unlocked with the Azuriom site currency.
- Purchases transfer coins from the buyer to the resource author inside a database transaction.
- Purchasing a resource unlocks its download, comments, and ratings.
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

### Moderation

- Optional approval queue for newly submitted or edited resources.
- A bypass permission allows trusted roles to publish without entering the queue.
- Dedicated administration page for pending resources, including publication date.
- Approval and rejection notifications are sent to the resource author.
- Rejections require a reason, which is included in the notification.
- Moderation tools are available directly from the resource page through a compact dropdown.
- Destructive and state-changing moderation actions require modal confirmation.
- Resources can be paused, archived, edited, permanently deleted, approved, or rejected.
- Paused resources cannot be downloaded, purchased, commented on, or rated.
- Archived resources are hidden through a global scope while their data remains stored.
- Moderators can delete individual comments, delete every comment by a user, and reset a resource's ratings.
- Moderators with resource-viewing tools can inspect published, pending, and rejected resources.

### Administration dashboard

The Marketplace administration menu contains dedicated pages for:

- Categories
- Pending resources
- Community reports
- Settings and marketplace statistics

The settings dashboard shows published resources, resources awaiting approval, and total coins spent on resource purchases. Administrators can also:

- Enable or disable publication moderation.
- Pause new resource submissions without preventing updates to existing resources.
- Pause new comments globally.
- Configure the maximum resource file size.
- Configure the resource file extension whitelist.

## Permissions

| Permission | Description |
| --- | --- |
| `marketplace.admin` | Access the Marketplace administration pages. |
| `marketplace.moderate` | View moderation states and approve or reject pending resources. |
| `marketplace.bypass-moderation` | Publish resources without entering the moderation queue. |
| `marketplace.archive` | Archive resources. |
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

Marketplace currently keeps all schema creation in one migration file, in accordance with the project's plugin-development policy:

```text
database/migrations/2026_08_24_000000_create_marketplace_tables.php
```

During development, if that migration has already run and the schema file was modified, roll back only the Marketplace migration and run migrations again from the Azuriom root:

```bash
php artisan migrate:rollback --path=plugins/marketplace/database/migrations
php artisan migrate --path=plugins/marketplace/database/migrations
```

Do not use this reset workflow on a production installation containing Marketplace data without first creating and verifying a database backup.

## Security notes

- Resource files and banners are stored on the private `local` filesystem disk and served through authorized controller actions.
- Resource descriptions are sanitized with an explicit HTML element, attribute, and URL-protocol allowlist.
- External destinations must use HTTP or HTTPS and require an explicit confirmation step.
- File extension checks are enforced server-side for resource creation, editing, and version updates; the browser file filter is only an additional usability aid.
- The permanent dangerous-extension denylist is enforced independently of the saved administrator whitelist.
- Paid unlocks use database transactions and row locks to reduce double-purchase and balance-race risks.

## Localization

Marketplace currently includes:

- English (`en`)
- Spanish (`es_ES`)

## Project structure

```text
marketplace/
├── database/migrations/   # Single Marketplace schema migration
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

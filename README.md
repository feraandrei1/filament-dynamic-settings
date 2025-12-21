# Filament Dynamic Settings

Centralized dynamic settings system for Filament with enum-driven setting management.

## Installation

Install the package via composer:

```bash
composer require ferarandrei1/filament-dynamic-settings
```

## Publishing Migration

Publish the migration file:

```bash
php artisan vendor:publish --tag=filament-dynamic-settings-migrations
```

Then run the migrations:

```bash
php artisan migrate
```

## Features

- Enum-driven setting names and groups
- Built-in Filament pages for General and Homepage settings
- Database-backed settings storage
- User-specific settings support
- No third-party dependencies

## Usage

### Using Built-in Pages

The package automatically registers two Filament pages:

1. **General Settings** - Manage domain settings, logo, and favicon
2. **Homepage Settings** - Configure homepage content, social media links, and contact information

These pages are automatically available in your Filament panel navigation.

### Using the Setting Model

```php
use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Illuminate\Support\Facades\Auth;

// Create or update a setting
Setting::updateOrCreate(
    [
        'uploaded_by_user_id' => Auth::id(),
        'group' => 'general',
        'name' => 'logo',
    ],
    ['payload' => $logoData]
);

// Retrieve settings
$settings = Setting::where('uploaded_by_user_id', Auth::id())
    ->where('group', 'general')
    ->get()
    ->keyBy('name');

$logo = $settings['logo']->payload;
```

### Using Enums

```php
use Feraandrei1\FilamentDynamicSettings\Enums\SettingName;
use Feraandrei1\FilamentDynamicSettings\Enums\SettingGroup;

// Get setting group from setting name
$group = SettingName::LOGO->group(); // Returns SettingGroup::GENERAL

// Use enum values
$settingName = SettingName::COMPANY_NAME->value; // 'company_name'
$groupName = SettingGroup::HOME_PAGE->value; // 'home_page'
```

### Extending Settings

You can extend the enums to add your own settings:

1. Create your own enums that extend the base enums
2. Add new cases with appropriate groups
3. Update the `group()` method to map new cases to groups

### Customizing Pages

To customize the built-in pages:

1. Extend the `GeneralSettings` or `HomePageSettings` classes
2. Override the `form()` method to modify the form schema
3. Register your custom page in your Filament panel configuration

## Available Settings

### General Settings

- `logo` - Main logo for the site
- `favicon` - Browser tab icon

### Homepage Settings

- `status` - Homepage enabled/disabled
- `company_name` - Company name
- `company_address` - Company address
- `description` - Company description
- `instagram_link` - Instagram URL
- `facebook_link` - Facebook URL
- `tiktok_link` - TikTok URL
- `email` - Contact email
- `phone_number` - Contact phone

## Requirements

- PHP 8.1 or higher
- Filament 3.0 or higher
- Laravel 10.0 or higher

## License

MIT License

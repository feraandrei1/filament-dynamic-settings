# Using Enums for Type-Safe Settings

This package provides enum-based setting management for type-safe and maintainable configuration.

## Overview

The package includes two main enums:
- `SettingGroup` - Defines logical groups for settings
- `SettingName` - Defines individual setting names and maps them to their groups

## Example: Setting Names and Groups

Here's an example of how to define setting names with automatic group mapping:

```php
<?php

namespace Feraandrei1\FilamentDynamicSettings\Enums;

enum SettingName: string
{
    // Home Page settings
    case STATUS = 'status';
    case COMPANY_NAME = 'company_name';
    case COMPANY_ADDRESS = 'company_address';
    case DESCRIPTION = 'description';

    case INSTAGRAM_LINK = 'instagram_link';
    case FACEBOOK_LINK = 'facebook_link';
    case TIKTOK_LINK = 'tiktok_link';

    case EMAIL = 'email';
    case PHONE_NUMBER = 'phone_number';

    // General settings
    case LOGO = 'logo';
    case FAVICON = 'favicon';

    public function group(): SettingGroup
    {
        return match($this) {

            // Home Page
            self::STATUS,
            self::COMPANY_NAME,
            self::COMPANY_ADDRESS,
            self::DESCRIPTION,

            self::INSTAGRAM_LINK,
            self::FACEBOOK_LINK,
            self::TIKTOK_LINK,

            self::EMAIL,
            self::PHONE_NUMBER => SettingGroup::HOME_PAGE,

            // General settings
            self::LOGO,
            self::FAVICON => SettingGroup::GENERAL,
        };
    }
}
```

## Basic Usage

### Getting Setting Values

```php
use Feraandrei1\FilamentDynamicSettings\Enums\SettingName;
use Feraandrei1\FilamentDynamicSettings\Enums\SettingGroup;

// Get the string value of a setting name
$settingName = SettingName::COMPANY_NAME->value; // 'company_name'

// Get the group for a setting
$group = SettingName::LOGO->group(); // Returns SettingGroup::GENERAL

// Get group value
$groupName = SettingGroup::HOME_PAGE->value; // 'home_page'
```

### Using Enums with the Setting Model

```php
use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Feraandrei1\FilamentDynamicSettings\Enums\SettingName;
use Illuminate\Support\Facades\Auth;

// Save a setting using enums
Setting::updateOrCreate(
    [
        'uploaded_by_user_id' => Auth::id(),
        'group' => SettingName::LOGO->group()->value,
        'name' => SettingName::LOGO->value,
    ],
    ['payload' => $logoData]
);

// Retrieve settings by group
$settings = Setting::where('uploaded_by_user_id', Auth::id())
    ->where('group', SettingGroup::GENERAL->value)
    ->get()
    ->keyBy('name');

$logo = $settings[SettingName::LOGO->value]->payload ?? null;
```

## Extending the Enums

To add your own settings, create custom enums that extend the functionality:

### 1. Create Your Own Setting Group

```php
<?php

namespace App\Enums;

enum CustomSettingGroup: string
{
    case GENERAL = 'general';
    case HOME_PAGE = 'home_page';
    case BILLING = 'billing';      // Your custom group
    case INTEGRATIONS = 'integrations';  // Your custom group
}
```

### 2. Create Your Own Setting Names

```php
<?php

namespace App\Enums;

enum CustomSettingName: string
{
    // Existing settings
    case LOGO = 'logo';
    case COMPANY_NAME = 'company_name';

    // Your new settings
    case PAYMENT_GATEWAY = 'payment_gateway';
    case API_KEY = 'api_key';
    case WEBHOOK_URL = 'webhook_url';

    public function group(): CustomSettingGroup
    {
        return match($this) {
            self::LOGO => CustomSettingGroup::GENERAL,
            self::COMPANY_NAME => CustomSettingGroup::HOME_PAGE,

            self::PAYMENT_GATEWAY,
            self::API_KEY => CustomSettingGroup::BILLING,

            self::WEBHOOK_URL => CustomSettingGroup::INTEGRATIONS,
        };
    }
}
```

### 3. Use Your Custom Enums

```php
use App\Enums\CustomSettingName;
use Feraandrei1\FilamentDynamicSettings\Models\Setting;

Setting::updateOrCreate(
    [
        'uploaded_by_user_id' => Auth::id(),
        'group' => CustomSettingName::API_KEY->group()->value,
        'name' => CustomSettingName::API_KEY->value,
    ],
    ['payload' => $apiKey]
);
```

## Benefits

- **Type Safety**: IDE autocomplete and type checking
- **Maintainability**: Centralized setting definitions
- **Self-Documenting**: Clear relationship between settings and groups
- **Refactoring**: Easy to rename settings across your codebase
- **Validation**: Compile-time checks prevent typos

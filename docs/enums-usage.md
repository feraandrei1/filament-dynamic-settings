# Using Enums for Type-Safe Settings

You can create enums in your application to manage settings in a type-safe way.

## Why Use Enums?

- **Type Safety**: IDE autocomplete and compile-time checks
- **No Typos**: Prevent string mistakes when referencing settings
- **Self-Documenting**: Clear relationship between settings and groups
- **Easy Refactoring**: Rename settings across your codebase safely

## Creating Your Setting Enums

### 1. Define Setting Groups

Create an enum for your setting groups:

```php
<?php

namespace App\Enums;

enum SettingGroup: string
{
    case GENERAL = 'general';
    case HOME_PAGE = 'home_page';
    case BILLING = 'billing';
}
```

### 2. Define Setting Names

Create an enum that maps setting names to their groups:

```php
<?php

namespace App\Enums;

enum SettingName: string
{
    // General settings
    case LOGO = 'logo';
    case FAVICON = 'favicon';

    // Home Page settings
    case COMPANY_NAME = 'company_name';
    case COMPANY_ADDRESS = 'company_address';
    case INSTAGRAM_LINK = 'instagram_link';

    // Billing settings
    case API_KEY = 'api_key';
    case WEBHOOK_URL = 'webhook_url';

    public function group(): SettingGroup
    {
        return match($this) {
            self::LOGO,
            self::FAVICON => SettingGroup::GENERAL,

            self::COMPANY_NAME,
            self::COMPANY_ADDRESS,
            self::INSTAGRAM_LINK => SettingGroup::HOME_PAGE,

            self::API_KEY,
            self::WEBHOOK_URL => SettingGroup::BILLING,
        };
    }
}
```

## Using Enums with Settings

### Saving Settings

```php
use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use App\Enums\SettingName;
use Illuminate\Support\Facades\Auth;

Setting::updateOrCreate(
    [
        'uploaded_by_user_id' => Auth::id(),
        'group' => SettingName::LOGO->group()->value,
        'name' => SettingName::LOGO->value,
    ],
    ['payload' => $logoData]
);
```

### Retrieving Settings

```php
use App\Enums\SettingGroup;
use App\Enums\SettingName;

$settings = Setting::where('uploaded_by_user_id', Auth::id())
    ->where('group', SettingGroup::GENERAL->value)
    ->get()
    ->keyBy('name');

$logo = $settings[SettingName::LOGO->value]->payload ?? null;
```

## Benefits Over Plain Strings

**Without Enums:**
```php
// Easy to make typos
Setting::where('group', 'gneral')->first(); // Bug!
$settings['lgo']->payload; // Bug!
```

**With Enums:**
```php
// Type-safe, IDE will catch errors
Setting::where('group', SettingGroup::GENERAL->value)->first();
$settings[SettingName::LOGO->value]->payload;
```

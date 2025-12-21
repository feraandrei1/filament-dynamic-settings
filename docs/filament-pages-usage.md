# Creating Custom Settings Pages

This package provides built-in Filament pages for managing settings. You can use these as-is or extend them to create your own custom settings pages.

## Overview

The package includes two built-in pages:
- `GeneralSettings` - For domain, logo, and favicon settings
- `HomePageSettings` - For homepage content and social media settings

These pages automatically register in your Filament panel navigation and handle saving settings to the database.

## Example: General Settings Page

Here's an example of a complete Filament settings page:

```php
<?php

namespace Feraandrei1\FilamentDynamicSettings\Filament\Pages;

use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class GeneralSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament-dynamic-settings::pages.general-settings';

    public $logo;
    public $favicon;

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $user_id = Auth::id();

        $settings = Setting::where('uploaded_by_user_id', $user_id)
            ->where('group', 'general')
            ->get()
            ->keyBy('name');

        $this->logo = $settings['logo']->payload ?? null;
        $this->favicon = $settings['favicon']->payload ?? null;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Branding')
                ->description('Upload and manage the visual identity for your site.')
                ->schema([
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo')
                        ->helperText('Main logo shown in navigation, login, and emails.'),

                    Forms\Components\FileUpload::make('favicon')
                        ->label('Favicon')
                        ->helperText('Small icon for browser tabs. Recommended: 32x32px.'),
                ])
                ->columns(2),
        ]);
    }

    public function save(): void
    {
        $user_id = Auth::id();
        $this->validate();

        $fields = ['logo', 'favicon'];

        foreach ($fields as $field) {
            Setting::updateOrCreate(
                [
                    'uploaded_by_user_id' => $user_id,
                    'group' => 'general',
                    'name' => $field,
                ],
                ['payload' => $this->$field]
            );
        }

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return __('General');
    }

    public function getTitle(): string
    {
        return __('General');
    }
}
```

## Key Components

### 1. Page Properties

Define public properties for each setting you want to manage:

```php
public $logo;
public $favicon;
public $company_name;
```

### 2. Loading Settings

Load existing settings from the database in the `mount()` method:

```php
public function mount(): void
{
    $this->loadSettings();
}

protected function loadSettings(): void
{
    $settings = Setting::where('uploaded_by_user_id', Auth::id())
        ->where('group', 'general')
        ->get()
        ->keyBy('name');

    $this->logo = $settings['logo']->payload ?? null;
    $this->favicon = $settings['favicon']->payload ?? null;
}
```

### 3. Form Schema

Define your form using Filament's form components:

```php
public function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Section::make('Branding')
            ->schema([
                Forms\Components\FileUpload::make('logo'),
                Forms\Components\TextInput::make('company_name'),
            ]),
    ]);
}
```

### 4. Saving Data

Handle form submission and save to the database:

```php
public function save(): void
{
    $user_id = Auth::id();
    $this->validate();

    foreach (['logo', 'favicon'] as $field) {
        Setting::updateOrCreate(
            [
                'uploaded_by_user_id' => $user_id,
                'group' => 'general',
                'name' => $field,
            ],
            ['payload' => $this->$field]
        );
    }

    Notification::make()
        ->title('Saved successfully')
        ->success()
        ->send();
}
```

## Creating Your Own Settings Page

### 1. Create a New Page Class

```php
<?php

namespace App\Filament\Pages;

use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CustomSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.custom-settings';

    public $api_key;
    public $webhook_url;
    public $enable_notifications;

    public function mount(): void
    {
        $settings = Setting::where('uploaded_by_user_id', Auth::id())
            ->where('group', 'integrations')
            ->get()
            ->keyBy('name');

        $this->api_key = $settings['api_key']->payload ?? null;
        $this->webhook_url = $settings['webhook_url']->payload ?? null;
        $this->enable_notifications = $settings['enable_notifications']->payload ?? false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('API Configuration')
                ->schema([
                    Forms\Components\TextInput::make('api_key')
                        ->label('API Key')
                        ->password()
                        ->required(),

                    Forms\Components\TextInput::make('webhook_url')
                        ->label('Webhook URL')
                        ->url(),

                    Forms\Components\Toggle::make('enable_notifications')
                        ->label('Enable Notifications'),
                ]),
        ]);
    }

    public function save(): void
    {
        $this->validate();

        foreach (['api_key', 'webhook_url', 'enable_notifications'] as $field) {
            Setting::updateOrCreate(
                [
                    'uploaded_by_user_id' => Auth::id(),
                    'group' => 'integrations',
                    'name' => $field,
                ],
                ['payload' => $this->$field]
            );
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return 'Integrations';
    }
}
```

### 2. Create the Blade View

Create a view file at `resources/views/filament/pages/custom-settings.blade.php`:

```blade
<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
```

## Customizing Built-in Pages

You can extend the built-in pages to customize their behavior:

```php
<?php

namespace App\Filament\Pages;

use Feraandrei1\FilamentDynamicSettings\Filament\Pages\GeneralSettings as BaseGeneralSettings;
use Filament\Forms\Form;
use Filament\Forms;

class CustomGeneralSettings extends BaseGeneralSettings
{
    public function form(Form $form): Form
    {
        return $form->schema([
            // Add your custom fields
            Forms\Components\Section::make('Additional Settings')
                ->schema([
                    Forms\Components\TextInput::make('site_title'),
                ]),

            // Include parent form fields
            ...parent::form($form)->getComponents(),
        ]);
    }

    protected function loadSettings(): void
    {
        parent::loadSettings();

        // Load your additional settings
        $settings = Setting::where('uploaded_by_user_id', Auth::id())
            ->where('group', 'general')
            ->get()
            ->keyBy('name');

        $this->site_title = $settings['site_title']->payload ?? null;
    }
}
```

## Available Form Components

Common Filament form components you can use:

- `TextInput::make()` - Text input field
- `Textarea::make()` - Multi-line text area
- `Toggle::make()` - On/off switch
- `FileUpload::make()` - File upload field
- `Select::make()` - Dropdown select
- `DatePicker::make()` - Date picker
- `RichEditor::make()` - Rich text editor
- `Section::make()` - Group fields in a section

See [Filament's Form Builder documentation](https://filamentphp.com/docs/forms) for more components.

## Tips

1. **Group Related Settings**: Use `Section::make()` to organize related fields
2. **Use Helper Text**: Add `helperText()` to explain what each setting does
3. **Validation**: Add validation rules using `->required()`, `->email()`, `->url()`, etc.
4. **Live Updates**: Use `->live()` for real-time form updates
5. **Navigation**: Control navigation order with `getNavigationSort()`
6. **Icons**: Choose appropriate icons from [Heroicons](https://heroicons.com)

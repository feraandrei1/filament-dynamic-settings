# Creating Custom Settings Pages

You can create custom Filament pages in your application to manage settings with a user-friendly interface.

## Basic Settings Page Structure

A settings page needs:
1. Public properties for each setting
2. A method to load settings from the database
3. A form schema defining the UI
4. A save method to persist changes

## Example: Creating a Settings Page

### 1. Create the Page Class

```php
<?php

namespace App\Filament\Pages;

use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class GeneralSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.general-settings';

    public $logo;
    public $favicon;

    public function mount(): void
    {
        $settings = Setting::where('uploaded_by_user_id', Auth::id())
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
                ->schema([
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo'),

                    Forms\Components\FileUpload::make('favicon')
                        ->label('Favicon'),
                ]),
        ]);
    }

    public function save(): void
    {
        $this->validate();

        foreach (['logo', 'favicon'] as $field) {
            Setting::updateOrCreate(
                [
                    'uploaded_by_user_id' => Auth::id(),
                    'group' => 'general',
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
}
```

### 2. Create the Blade View

Create `resources/views/filament/pages/general-settings.blade.php`:

```blade
<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </form>
</x-filament-panels::page>
```

## Common Form Components

Use Filament's form components to build your settings UI:

```php
// Text input
Forms\Components\TextInput::make('site_name')
    ->required()
    ->maxLength(255)

// Textarea
Forms\Components\Textarea::make('description')
    ->rows(3)

// Toggle switch
Forms\Components\Toggle::make('maintenance_mode')

// File upload
Forms\Components\FileUpload::make('logo')
    ->image()

// Select dropdown
Forms\Components\Select::make('theme')
    ->options([
        'light' => 'Light',
        'dark' => 'Dark',
    ])
```

## Organizing with Sections

Group related settings using sections:

```php
public function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Section::make('General')
            ->description('Basic site settings')
            ->schema([
                Forms\Components\TextInput::make('site_name'),
                Forms\Components\TextInput::make('site_url'),
            ]),

        Forms\Components\Section::make('Email')
            ->description('Email configuration')
            ->schema([
                Forms\Components\TextInput::make('email_from'),
                Forms\Components\TextInput::make('email_name'),
            ]),
    ]);
}
```

## Using with Enums

Combine with enums for type-safe setting management:

```php
use App\Enums\SettingName;
use App\Enums\SettingGroup;

public function mount(): void
{
    $settings = Setting::where('uploaded_by_user_id', Auth::id())
        ->where('group', SettingGroup::GENERAL->value)
        ->get()
        ->keyBy('name');

    $this->logo = $settings[SettingName::LOGO->value]->payload ?? null;
}

public function save(): void
{
    $this->validate();

    Setting::updateOrCreate(
        [
            'uploaded_by_user_id' => Auth::id(),
            'group' => SettingName::LOGO->group()->value,
            'name' => SettingName::LOGO->value,
        ],
        ['payload' => $this->logo]
    );
}
```

## Tips

- Add `->live()` to fields for real-time updates
- Use `->helperText()` to explain what each setting does
- Add validation rules like `->required()`, `->email()`, `->url()`
- Use `Section::make()` to organize related fields
- Set navigation order with `protected static ?int $navigationSort = 10`

## Learn More

See [Filament's Form Builder documentation](https://filamentphp.com/docs/forms) for more components and features.

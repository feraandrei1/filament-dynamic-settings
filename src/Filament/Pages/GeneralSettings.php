<?php

namespace Feraandrei1\FilamentDynamicSettings\Filament\Pages;

use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class GeneralSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament-dynamic-settings::pages.general-settings';

    public $domain;
    public $custom_domain;

    public $logo;
    public $favicon;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

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

        $homePageLink = route('gallery.index', ['user' => Auth::user()->username]);
        $this->domain = Str::replace('https://', '', $homePageLink);
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Group::make()

                ->schema([

                    Forms\Components\Section::make('Domain Settings')
                        ->description('Manage system-assigned and custom domains for this site.')
                        ->schema([

                            Forms\Components\TextInput::make('domain')
                                ->label('System Domain')
                                ->helperText(new HtmlString(
                                    'The default domain automatically assigned by the system using your company name.
                                     If you want to change it, you can update the company name from the
                                     <a href="' . route('filament.app.auth.profile') . '" class="text-primary-500 underline">profile settings</a>.'
                                ))
                                ->disabled()
                                ->extraAttributes(function ($state) {
                                    return [
                                        'x-on:click' => 'window.navigator.clipboard.writeText("' . $state . '"); $tooltip("Copied to clipboard", { timeout: 1500 });',
                                    ];
                                })
                                ->suffixAction(
                                    Action::make('copy')->icon('heroicon-m-clipboard')
                                ),

                            Forms\Components\TextInput::make('custom_domain')
                                ->label('Custom Domain')
                                ->helperText(new HtmlString(
                                    'A custom domain you configured for this site. Must be set up in your DNS settings first. If you need help,
                                     <a href="' . route('filament.app.pages.contact-form') . '" class="text-primary-500 underline">contact support</a>.'
                                ))
                                ->disabled(),

                        ])->columnSpan(1),

                ])->columns(2),

            Forms\Components\Group::make()

                ->schema([

                    Forms\Components\Section::make('Branding')
                        ->description('Upload and manage the visual identity for your site.')
                        ->schema([

                            Forms\Components\FileUpload::make('logo')
                                ->label('Logo')
                                ->helperText('Main logo shown in navigation, login, and emails. Use a transparent PNG or SVG for best results.'),

                            Forms\Components\FileUpload::make('favicon')
                                ->label('Favicon')
                                ->helperText('Small icon for browser tabs and bookmarks. Recommended size: 32x32 or 64x64 pixels.'),

                        ])
                        ->columnSpan(1)
                        ->columns(2),

                ])->columns(2),

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

    public function getSubheading(): ?string
    {
        return "Configure your site's general settings, including domains, logo, and favicon.";
    }

    public static function getNavigationSort(): int
    {
        return 3;
    }
}

<?php

namespace Feraandrei1\FilamentDynamicSettings\Filament\Pages;

use Feraandrei1\FilamentDynamicSettings\Models\Setting;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Actions\Action as ComponentsAction;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class HomePageSettings extends Page implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-window';

    protected static string $view = 'filament-dynamic-settings::pages.home-page-settings';

    public $status;
    public $company_name;
    public $company_address;
    public $description;
    public $instagram_link;
    public $facebook_link;
    public $tiktok_link;
    public $email;
    public $phone_number;

    public $home_page_url;

    public $preview_field;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('home')
                ->label('View site')
                ->visible(fn(): bool => $this->status && $this->company_name)
                ->url(url: $this->home_page_url, shouldOpenInNewTab: true)
        ];
    }

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $user_id = Auth::id();

        $settings = Setting::where('uploaded_by_user_id', $user_id)
            ->where('group', 'home_page')
            ->get()
            ->keyBy('name');

        $this->status = $settings['status']->payload ?? false;
        $this->company_name = $settings['company_name']->payload ?? null;
        $this->company_address = $settings['company_address']->payload ?? null;
        $this->description = $settings['description']->payload ?? null;
        $this->instagram_link = $settings['instagram_link']->payload ?? null;
        $this->facebook_link = $settings['facebook_link']->payload ?? null;
        $this->tiktok_link = $settings['tiktok_link']->payload ?? null;
        $this->email = $settings['email']->payload ?? null;
        $this->phone_number = $settings['phone_number']->payload ?? null;

        $this->home_page_url = route('gallery.index', ['user' => Auth::user()->username]);
    }

    public function form(Form $form): Form
    {
        $data = [
            'username' => Auth::user()->username,

            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'description' => $this->description,
            'instagram_link' => $this->instagram_link,
            'facebook_link' => $this->facebook_link,
            'tiktok_link' => $this->tiktok_link,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
        ];

        return $form

            ->schema([

                Forms\Components\Group::make()

                    ->schema([

                        Forms\Components\Section::make('Homepage Status')
                            ->description('Use this toggle to enable or disable the homepage.')
                            ->schema([
                                Forms\Components\Toggle::make('status'),
                            ]),

                        Forms\Components\Section::make('Home page link')
                            ->description('Direct link to your public home page. You can copy it to share with others.')
                            ->schema([

                                Forms\Components\TextInput::make('home_page_url')
                                    ->label('URL')
                                    ->disabled()
                                    ->extraAttributes(function ($state) {
                                        return [
                                            'x-on:click' => 'window.navigator.clipboard.writeText("' . $state . '"); $tooltip("Copied to clipboard", { timeout: 1500 });',
                                        ];
                                    })
                                    ->suffixAction(
                                        ComponentsAction::make('copy')->icon('heroicon-m-clipboard')
                                    ),
                            ]),

                        Forms\Components\Section::make('Company')
                            ->description('Basic information about your company that will appear on the home page.')
                            ->schema([

                                Forms\Components\TextInput::make('company_name')
                                    ->required()
                                    ->label('Name')
                                    ->maxLength(255)
                                    ->placeholder('Example Corporation LTD')
                                    ->live()
                                    ->translateLabel(),

                                Forms\Components\Textarea::make('company_address')
                                    ->label('Address')
                                    ->rows(2)
                                    ->maxLength(256)
                                    ->placeholder('123 Main Street, Springfield, USA')
                                    ->live()
                                    ->translateLabel(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2)
                                    ->maxLength(256)
                                    ->placeholder('Leading provider of industrial solutions since 1998')
                                    ->live()
                                    ->translateLabel(),
                            ]),

                        Forms\Components\Section::make('Social Media')
                            ->description('Links to your social media profiles that will be shown on your page.')
                            ->schema([

                                Forms\Components\TextInput::make('instagram_link')
                                    ->label('Instagram')
                                    ->url()
                                    ->placeholder('https://www.instagram.com/username')
                                    ->live()
                                    ->translateLabel(),

                                Forms\Components\TextInput::make('facebook_link')
                                    ->label('Facebook')
                                    ->url()
                                    ->placeholder('https://www.facebook.com/username')
                                    ->live()
                                    ->translateLabel(),

                                Forms\Components\TextInput::make('tiktok_link')
                                    ->label('TikTok')
                                    ->url()
                                    ->placeholder('https://www.tiktok.com/username')
                                    ->live()
                                    ->translateLabel(),

                            ]),

                        Forms\Components\Section::make('Contact')
                            ->description('Provide your preferred contact details for customers to reach you.')
                            ->schema([

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->maxLength(255)
                                    ->email()
                                    ->placeholder('example@gmail.com')
                                    ->live()
                                    ->translateLabel(),

                                Forms\Components\TextInput::make('phone_number')
                                    ->label('Phone number')
                                    ->maxLength(255)
                                    ->placeholder('+40760123456')
                                    ->regex('/^[0-9\s\-\+\(\)]+$/')
                                    ->live()
                                    ->translateLabel(),

                            ])->columns(2),

                    ])->columnSpan(1),

                Forms\Components\Group::make()

                    ->schema([

                        Forms\Components\Section::make('Preview')
                            ->description('Live preview of how your home page will look with the information provided.')
                            ->schema([

                                Forms\Components\ViewField::make('preview_field')
                                    ->view('filament.view-fields.preview-home-page', [
                                        'user' => Auth::user()->username,
                                        'data' => $data,
                                    ])
                                    ->nullable()
                                    ->visible(fn(): bool => $this->company_name ?? false)
                                    ->translateLabel(),

                                Forms\Components\Placeholder::make('preview_message')
                                    ->label('')
                                    ->hidden(fn(): bool => $this->company_name ?? false)
                                    ->content('Please fill the "Name" field to see a live preview.')

                            ]),

                    ])->columnSpan(1),

            ])->columns(2);
    }

    public function save(): void
    {
        $user_id = Auth::id();

        $this->validate();

        $fields = [

            'status',
            'company_name',
            'company_address',
            'description',

            'instagram_link',
            'facebook_link',
            'tiktok_link',

            'email',
            'phone_number',
        ];

        foreach ($fields as $field) {
            Setting::updateOrCreate(
                [
                    'uploaded_by_user_id' => $user_id,
                    'group' => 'home_page',
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
        return __('Homepage');
    }

    public function getTitle(): string
    {
        return __('Homepage');
    }

    public function getSubheading(): ?string
    {
        return 'Customize your homepage. Any field you leave empty will not appear on the page.';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }
}

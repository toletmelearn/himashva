<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static string $view = 'filament.pages.manage-site-settings';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::query()->pluck('value', 'key')->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->schema([
                                Forms\Components\TextInput::make('site_name'),
                                Forms\Components\TextInput::make('tagline'),
                                Forms\Components\FileUpload::make('logo')->image()->directory('settings')->disk('public'),
                                Forms\Components\FileUpload::make('favicon')->image()->directory('settings')->disk('public'),
                                Forms\Components\TextInput::make('contact_email')->email(),
                                Forms\Components\TextInput::make('contact_phone'),
                                Forms\Components\Textarea::make('address'),
                                Forms\Components\TextInput::make('whatsapp_number'),
                                Forms\Components\TextInput::make('announcement_text'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Social')
                            ->schema([
                                Forms\Components\TextInput::make('social_instagram'),
                                Forms\Components\TextInput::make('social_facebook'),
                                Forms\Components\TextInput::make('social_pinterest'),
                                Forms\Components\TextInput::make('social_youtube'),
                                Forms\Components\TextInput::make('social_twitter'),
                                Forms\Components\TextInput::make('social_linkedin'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Payment')
                            ->schema([
                                Forms\Components\TextInput::make('razorpay_key_id'),
                                Forms\Components\TextInput::make('razorpay_key_secret')->password()->revealable(),
                                Forms\Components\Toggle::make('cod_enabled'),
                                Forms\Components\Toggle::make('upi_enabled'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Shipping')
                            ->schema([
                                Forms\Components\TextInput::make('free_shipping_threshold')->numeric(),
                                Forms\Components\TextInput::make('flat_shipping_rate')->numeric(),
                                Forms\Components\TextInput::make('min_order_amount')->numeric(),
                                Forms\Components\TextInput::make('shiprocket_email'),
                                Forms\Components\TextInput::make('shiprocket_password')->password()->revealable(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Tax')
                            ->schema([
                                Forms\Components\TextInput::make('default_tax_rate')
                                    ->label('Default GST Rate')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(18),
                                Forms\Components\TextInput::make('gst_number')
                                    ->label('GSTIN')
                                    ->helperText('Shown on PDF invoices.'),
                            ]),
                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title'),
                                Forms\Components\Textarea::make('meta_description'),
                                Forms\Components\TextInput::make('google_analytics_id'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Email')
                            ->schema([
                                Forms\Components\Toggle::make('send_order_emails')
                                    ->label('Send order lifecycle emails')
                                    ->default(true),
                            ]),
                        Forms\Components\Tabs\Tab::make('Marketing')
                            ->schema([
                                Forms\Components\Toggle::make('abandoned_cart_recovery_enabled')
                                    ->label('Send abandoned cart recovery emails')
                                    ->default(false),
                                Forms\Components\TextInput::make('abandoned_cart_coupon_code')
                                    ->label('Recovery Coupon Code')
                                    ->nullable(),
                                Forms\Components\TextInput::make('abandoned_cart_coupon_text')
                                    ->label('Recovery Coupon Text')
                                    ->nullable(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $groups = [
            'site_name' => 'general', 'tagline' => 'general', 'logo' => 'general', 'favicon' => 'general',
            'contact_email' => 'general', 'contact_phone' => 'general', 'address' => 'general',
            'whatsapp_number' => 'general', 'announcement_text' => 'general',
            'social_instagram' => 'social', 'social_facebook' => 'social', 'social_pinterest' => 'social',
            'social_youtube' => 'social', 'social_twitter' => 'social', 'social_linkedin' => 'social',
            'razorpay_key_id' => 'payment', 'razorpay_key_secret' => 'payment', 'cod_enabled' => 'payment', 'upi_enabled' => 'payment',
            'free_shipping_threshold' => 'shipping', 'flat_shipping_rate' => 'shipping', 'min_order_amount' => 'shipping',
            'shiprocket_email' => 'shipping', 'shiprocket_password' => 'shipping',
            'default_tax_rate' => 'tax', 'gst_number' => 'tax',
            'meta_title' => 'seo', 'meta_description' => 'seo', 'google_analytics_id' => 'seo',
            'send_order_emails' => 'email',
            'abandoned_cart_recovery_enabled' => 'marketing', 'abandoned_cart_coupon_code' => 'marketing',
            'abandoned_cart_coupon_text' => 'marketing',
        ];

        foreach ($this->form->getState() as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'group' => $groups[$key] ?? 'general']
            );
        }

        Notification::make()->title('Settings saved')->success()->send();
    }
}

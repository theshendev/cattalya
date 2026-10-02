<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'تنظیمات سایت';

    protected static ?string $title = 'تنظیمات سایت';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.manage-settings';

    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            'site_name' => Setting::get('site_name', 'کاتالیا'),
            'site_name_en' => Setting::get('site_name_en', 'Cattalya'),
            'site_description' => Setting::get('site_description'),
            'footer_copyright' => Setting::get('footer_copyright'),
            'contact_phone' => Setting::get('contact_phone'),
            'contact_email' => Setting::get('contact_email'),
            'instagram_url' => Setting::get('instagram_url'),
            'whatsapp_number' => Setting::get('whatsapp_number'),
            'free_shipping_threshold' => Setting::get('free_shipping_threshold', '12000000'),
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات سایت')->schema([
                    Forms\Components\TextInput::make('site_name')->label('نام سایت (فارسی)')->required(),
                    Forms\Components\TextInput::make('site_name_en')->label('نام سایت (انگلیسی)')->required(),
                    Forms\Components\Textarea::make('site_description')->label('درباره ما')->rows(3)->columnSpanFull(),
                    Forms\Components\Textarea::make('footer_copyright')->label('متن کپی‌رایت')->rows(2)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('راه‌های ارتباطی')->schema([
                    Forms\Components\TextInput::make('contact_phone')->label('تلفن تماس'),
                    Forms\Components\TextInput::make('contact_email')->label('ایمیل'),
                    Forms\Components\TextInput::make('instagram_url')->label('لینک اینستاگرام')->url(),
                    Forms\Components\TextInput::make('whatsapp_number')->label('شماره واتساپ'),
                ])->columns(2),

                Forms\Components\Section::make('تنظیمات فروشگاه')->schema([
                    Forms\Components\TextInput::make('free_shipping_threshold')
                        ->label('حد ارسال رایگان (تومن)')
                        ->numeric(),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('تنظیمات با موفقیت ذخیره شد')
            ->success()
            ->send();
    }
}

<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\CouponResource;
use App\Filament\Resources\GoldPriceResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\GoldPriceWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\SalesChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Http\Middleware\SetAdminLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#8a6300'),
                'gray' => Color::Stone,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('17rem')
            ->maxContentWidth(MaxWidth::Full)
            ->navigationGroups([
                NavigationGroup::make('فروشگاه')->icon('heroicon-o-building-storefront'),
                NavigationGroup::make('بازاریابی')->icon('heroicon-o-megaphone'),
                NavigationGroup::make('محتوای سایت')->icon('heroicon-o-rectangle-stack'),
                NavigationGroup::make('ارتباطات')->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::make('تنظیمات')->icon('heroicon-o-cog-6-tooth'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => Blade::render('@include("filament.admin-styles")'),
            )
            ->brandName('کاتالیا | پنل مدیریت')
            ->favicon(asset('favicon.ico'))
            ->font('Vazirmatn', 'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css')
            ->resources([
                ProductResource::class,
                CategoryResource::class,
                BannerResource::class,
                OrderResource::class,
                GoldPriceResource::class,
                AnnouncementResource::class,
                CouponResource::class,
                ContactMessageResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
                ManageSettings::class,
            ])
            ->widgets([
                StatsOverviewWidget::class,
                GoldPriceWidget::class,
                SalesChartWidget::class,
                LatestOrdersWidget::class,
            ])
            ->middleware([
                SetAdminLocale::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

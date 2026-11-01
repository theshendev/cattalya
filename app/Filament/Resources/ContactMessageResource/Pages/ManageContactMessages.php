<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageContactMessages extends ManageRecords
{
    protected static string $resource = ContactMessageResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه')->badge(ContactMessage::count()),
            'unread' => Tab::make('خوانده‌نشده')
                ->icon('heroicon-o-envelope')
                ->badge(ContactMessage::whereNull('read_at')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('read_at')),
        ];
    }
}

<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\SettingResource\Schemas\SettingForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * One tab per settings group, after an "all" tab.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make(__('admin.setting_groups.all'))];

        foreach (SettingForm::groupOptions() as $group => $label) {
            $tabs[$group] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', $group));
        }

        return $tabs;
    }
}

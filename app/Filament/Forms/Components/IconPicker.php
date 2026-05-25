<?php

namespace App\Filament\Forms\Components;

use App\Support\SplashIcons;
use Filament\Forms\Components\Field;

class IconPicker extends Field
{
    protected string $view = 'filament.forms.components.icon-picker';

    public function icons(): array
    {
        return SplashIcons::all();
    }
}

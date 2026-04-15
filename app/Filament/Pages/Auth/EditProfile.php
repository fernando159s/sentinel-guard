<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function mount(): void
    {
        $tenant = Filament::getTenant();
        $ruc = $tenant?->ruc ?? '';

        $this->redirect("/admin/{$ruc}/mi-perfil");
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}

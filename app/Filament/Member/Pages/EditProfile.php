<?php

namespace App\Filament\Member\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin cá nhân')->schema([
                $this->getNameFormComponent(),
                TextInput::make('phone')->label('Điện thoại cá nhân')->tel()->maxLength(30),
                $this->getEmailFormComponent()->helperText('Thay đổi email đăng nhập cần xác nhận qua email mới.'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Bảo mật tài khoản')->schema([
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ])->columnSpanFull(),
        ]);
    }
}

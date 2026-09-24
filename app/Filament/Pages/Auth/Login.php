<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'Welcome back to Himashva';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Sign in to manage your store.';
    }
}

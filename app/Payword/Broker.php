<?php

namespace App\Payword;

use App\Models\User;

class Broker {

    public static function blockMoney(Certificate $certificate)
    {
        $identity = $certificate->getUserIdentity();

        if (! $user = User::where('email', $identity)->first()) {
            return false;
        }

        if ($user->balance < $certificate->getCreditLimit()) {
            return false;
        }

        $user->blockMoney($certificate->getCreditLimit());

        return true;
    }
}

<?php

namespace App\Payword;

use App\Models\User;
use App\Models\SerialNumber;

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

    // V → B: commit(U), cl, l
    public static function redeem($commits, $userIdentity, $vendorIdentity)
    {
        $amount = 0;
        $totalPrice = 0;
        $serialNo = null;

        foreach ($commits as $model) {
            $commit = new Commit($model->commit);

            $totalPrice += ($commit->getHashChainLength() - 1) * $commit->getPrice();

            if (! $commit->verify()) {
                return false;
            }

            if (! $serialNo = SerialNumber::find($commit->getCertificate()->getSerialNumber())) {
                return false;
            }

            $payword = $model->last_payword; // cl
            $lastPaywordPos = $model->last_payword_pos; // l

            for ($i=0; $i < $lastPaywordPos; $i++) {
                $payword = sha1($payword);
            }

            if ($payword === $commit->getFirstPayword()) {
                $amount += $commit->getPrice() * $lastPaywordPos;
            }

            $model->delete();
        }

        $user = User::where('email', $userIdentity)->first();
        $vendor = User::where('email', $vendorIdentity)->first();

        self::transfer($user, $vendor, $amount);

        $user->unblockMoney($totalPrice - $amount);

        $serialNo->delete();
    }

    public static function transfer($user, $vendor, $amount)
    {
        $user->withdrawBlockedMoney($amount);
        $vendor->deposit($amount);
    }
}

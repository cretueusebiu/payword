<?php

namespace App\Payword;

use Carbon\Carbon;

class Certificate {

    /// B, U, KB, KU, exp, info
    protected $brokerIdentity;
    protected $userIdentity;
    protected $brokerPublicKey;
    protected $userPublicKey;
    protected $creditLimit;
    protected $expireDate;
    protected $serialNumber;

    protected $signature;

    /**
     * Create a new certificate instance.
     *
     * @return void
     */
    public function __construct(
        $brokerIdentity, $userIdentity, $brokerPublicKey, $userPublicKey,
        $creditLimit, $expireDate, $serialNumber, $signature = null
    ) {
        $this->brokerIdentity = $brokerIdentity;
        $this->userIdentity = $userIdentity;
        $this->brokerPublicKey = $brokerPublicKey;
        $this->userPublicKey = $userPublicKey;
        $this->creditLimit = $creditLimit;
        $this->signature = $signature;

        if (! $expireDate instanceOf Carbon) {
            $expireDate = Carbon::createFromTimestamp($expireDate);
        }

        $this->expireDate = $expireDate;
        $this->serialNumber = $serialNumber;
    }

    public function sign($privkeyPath)
    {
        $data = $this->getData();

        $pkeyid = openssl_pkey_get_private('file://'.$privkeyPath);
        openssl_sign($data, $signature, $pkeyid, 'sha1WithRSAEncryption');

        $this->signature = bin2hex($signature);
    }

    public function verify()
    {
        $signature = hex2bin($this->signature);
        $pubKeyFile = tempnam(sys_get_temp_dir(), 'pubkey');

        file_put_contents($pubKeyFile, $this->brokerPublicKey);

        $pubkeyid = openssl_pkey_get_public('file://'.$pubKeyFile);
        $ok = openssl_verify($this->getData(), $signature, $pubkeyid);

        unlink($pubKeyFile);

        return $ok === 1;
    }

    public function getUserPublicKey()
    {
        return $this->userPublicKey;
    }

    public function getUserIdentity()
    {
        return $this->userIdentity;
    }

    public function getCreditLimit()
    {
        return $this->creditLimit;
    }

    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    protected function getData()
    {
        $data = '';
        $data .= str_pad($this->brokerIdentity, Constants::IDENTITY_LENGTH);
        $data .= str_pad($this->userIdentity, Constants::IDENTITY_LENGTH);
        $data .= $this->brokerPublicKey;
        $data .= $this->userPublicKey;
        $data .= str_pad($this->creditLimit, Constants::PRICE_LENGTH);
        $data .= $this->expireDate->timestamp;
        $data .= $this->serialNumber;

        return $data;
    }

    public function toString()
    {
        return $this->getData() . $this->signature;
    }

    public static function decode($certificate)
    {
        $pos = 0;

        $brokerIdentity = trim(substr($certificate, $pos, Constants::IDENTITY_LENGTH));
        $pos += Constants::IDENTITY_LENGTH;

        $userIdentity = trim(substr($certificate, $pos, Constants::IDENTITY_LENGTH));
        $pos += Constants::IDENTITY_LENGTH;

        $brokerPublicKey = trim(substr($certificate, $pos, Constants::RSA_KEY_LENGTH));
        $pos += Constants::RSA_KEY_LENGTH;

        $userPublicKey = trim(substr($certificate, $pos, Constants::RSA_KEY_LENGTH));
        $pos += Constants::RSA_KEY_LENGTH;

        $creditLimit = (int) trim(substr($certificate, $pos, Constants::PRICE_LENGTH));
        $pos += Constants::PRICE_LENGTH;

        $expireDate = (int) trim(substr($certificate, $pos, Constants::DATE_LENGTH));
        $pos += Constants::DATE_LENGTH;

        $serialNumber = (int) trim(substr($certificate, $pos, Constants::SERIAL_NO_LENGTH));
        $pos += Constants::SERIAL_NO_LENGTH;

        $signature = trim(substr($certificate, $pos, Constants::SINGATURE_LENGTH));

        return new static(
            $brokerIdentity, $userIdentity, $brokerPublicKey, $userPublicKey,
            $creditLimit, $expireDate, $serialNumber, $signature
        );
    }
}

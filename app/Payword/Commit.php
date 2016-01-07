<?php

namespace App\Payword;

class Commit {

    protected $vendorIdentity;
    protected $certificate;
    protected $firstPayword;
    protected $date;
    protected $hashChainLength;
    protected $price;
    protected $bookId;
    protected $signature;

    /**
     * @var string
     */
    protected $commit;

    /**
     * Create a new commit instance.
     *
     * @param  string $commit
     * @return void
     */
    public function __construct($commit)
    {
        $this->commit = $commit;

        $pos = 0;

        $this->vendorIdentity = trim(substr($commit, $pos, Constants::IDENTITY_LENGTH));
        $pos += Constants::IDENTITY_LENGTH;

        $certificate = trim(substr($commit, $pos, Constants::CERTIFICATE_LENGTH));
        $this->certificate = Certificate::decode($certificate);
        $pos += Constants::CERTIFICATE_LENGTH;

        $this->firstPayword = trim(substr($commit, $pos, Constants::PAYWORD_LENGTH));
        $pos += Constants::PAYWORD_LENGTH;

        $this->date = (int) trim(substr($commit, $pos, Constants::DATE_LENGTH));
        $pos += Constants::DATE_LENGTH;

        $this->hashChainLength = (int) trim(substr($commit, $pos, Constants::HASH_CHAIN_LENGTH));
        $pos += Constants::HASH_CHAIN_LENGTH;

        $this->price = (int) trim(substr($commit, $pos, Constants::PRICE_LENGTH));
        $pos += Constants::PRICE_LENGTH;

        $this->bookId = (int) trim(substr($commit, $pos, Constants::BOOK_ID_LENGTH));
        $pos += Constants::BOOK_ID_LENGTH;

        $this->signature = trim(substr($commit, $pos, Constants::SINGATURE_LENGTH));
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function getFirstPayword()
    {
        return $this->firstPayword;
    }

    public function getHashChainLength()
    {
        return $this->hashChainLength;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getBookId()
    {
        return $this->bookId;
    }

    public function toString()
    {
        return $this->commit;
    }

    /**
     * Verify commit.
     *
     * @return bool
     */
    public function verify()
    {
        if (! $this->verifyBrokerCertificateSignature()) {
            return false;
        }

        if (! $this->verifyUserCommitSignature()) {
            return false;
        }

        return true;
    }

    /**
     * Verify broker certificate signature.
     *
     * @return bool
     */
    protected function verifyBrokerCertificateSignature()
    {
        return $this->certificate->verify();
    }

    /**
     * Verify user commit signature.
     *
     * @return bool
     */
    protected function verifyUserCommitSignature()
    {
        $signature = hex2bin($this->signature);
        $pubKeyFile = tempnam(sys_get_temp_dir(), 'pubkey');

        file_put_contents($pubKeyFile, $this->certificate->getUserPublicKey());

        $pubkeyid = openssl_pkey_get_public('file://'.$pubKeyFile);
        $ok = openssl_verify($this->getData(), $signature, $pubkeyid);

        unlink($pubKeyFile);

        return $ok === 1;
    }

    protected function getData()
    {
        return substr($this->commit, 0, strlen($this->commit) - Constants::SINGATURE_LENGTH);
    }
}

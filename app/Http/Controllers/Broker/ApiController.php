<?php

namespace App\Http\Controllers\Broker;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function getIdentity()
    {
        return 'broker@broker.payword.app';
    }

    /**
     * Show the RSA public key.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPublicKey()
    {
        return file_get_contents(storage_path('rsa_keys/rsa_pub.pem'));
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function me()
    {
        return $this->auth->user();
    }

    /**
     * Setp 1, generate certificate
     * B → U: C(U) = sigB (B, U, KB, KU, exp, info)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validateRequest($request);

        $userIdentity = trim($request->identity);
        $userPublicKey = trim($request->public_key);

        $brokerIdentity = $this->getIdentity();
        $brokerPublicKey = $this->getPublicKey();

        $expireDate = Carbon::now()->addDay();
        $creditLimit = $request->credit_limit;

        $userSignature = trim($request->signature);
        if (! $this->verifyIdentity($userIdentity, $userSignature)) {
            return response()->json('Invalid user identity.', 422);
        }

        $data = '';
        $data .= str_pad($brokerIdentity, 100);
        $data .= str_pad($userIdentity, 100);
        $data .= $brokerPublicKey;
        $data .= $userPublicKey;
        $data .= str_pad($creditLimit, 10);
        $data .= $expireDate->timestamp;

        // Sign the data.
        $pkeyid = openssl_pkey_get_private('file://'.storage_path('rsa_keys/rsa_priv.pem'));
        openssl_sign($data, $signature, $pkeyid, 'sha1WithRSAEncryption');

        $certificate = $data . base64_encode($signature);

        return $certificate;
    }

    /**
     * Verify user identity.
     *
     * @param  string $userIdentity
     * @param  string $userSignature
     * @return bool
     */
    protected function verifyIdentity($userIdentity, $userSignature)
    {
        if (! $user = User::where('email', $userIdentity)->first()) {
            return false;
        }

        $signature = hex2bin($userSignature);

        $pubKeyFile = tempnam(sys_get_temp_dir(), 'pubkey');

        file_put_contents($pubKeyFile, $user->public_key);

        $pubkeyid = openssl_pkey_get_public('file://'.$pubKeyFile);

        $ok = openssl_verify($userIdentity, $signature, $pubkeyid);

        unlink($pubKeyFile);

        return $ok === 1;
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateRequest(Request $request)
    {
        $this->validate($request, [
            'signature' => 'required',
            'identity' => 'required|email|exists:users,email',
            'public_key' => 'required',
            'credit_limit' => 'required|min:1',
        ]);
    }
}

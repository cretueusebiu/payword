<?php

namespace App\Http\Controllers\Broker;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Payword\Broker;
use App\Payword\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    /**
     * Get the broker identity.
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'broker@broker.payword.app';
    }

    /**
     * Get the broker public RSA key.
     *
     * @return string
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

        // Verify user identity.
        $userSignature = trim($request->signature);
        if (! $this->verifyIdentity($userIdentity, $userSignature)) {
            return response()->json('Invalid user identity.', 422);
        }

        $privKeyPath = storage_path('rsa_keys/rsa_priv.pem');

        $certificate =  new Certificate(
            $brokerIdentity, $userIdentity, $brokerPublicKey,
            $userPublicKey, $creditLimit, $expireDate
        );

        $certificate->sign($privKeyPath);

        return $certificate->toString();
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

    /**
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function blockMoney(Request $request)
    {
        // TODO: verify vendor identity

        $certificate = Certificate::decode($request->certificate);

        $success = Broker::blockMoney($certificate);

        return response()->json(compact('success'));
    }
}

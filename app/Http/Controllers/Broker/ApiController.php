<?php

namespace App\Http\Controllers\Broker;

use Auth;
use Carbon\Carbon;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Auth\AuthManager
     * @return void
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth->guard('api');

        $this->middleware('auth:api');
    }

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

        // Update user's public key.
        $this->auth->user()->public_key = $request->public_key;
        $this->auth->user()->save();

        $userIdentity = $request->identity;
        $userPublicKey = $request->public_key;

        $brokerIdentity = $this->getIdentity();
        $brokerPublicKey = $this->getPublicKey();

        $expireDate = Carbon::now()->addDay();
        $creditLimit = $request->credit_limit;

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
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateRequest(Request $request)
    {
        $this->validate($request, [
            'identity' => 'required|email|exists:users,email,id,'.$this->auth->user()->id,
            'public_key' => 'required',
            'credit_limit' => 'required|min:1',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Broker;

use Auth;
use App\Http\Controller;
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
    }

    public function me()
    {
        return $this->auth->user();
    }
}

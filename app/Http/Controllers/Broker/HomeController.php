<?php

namespace App\Http\Controllers\Broker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('broker.home');
    }

    /**
     * Show the settings form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSettings()
    {
        return view('broker.user.settings');
    }

    /**
     * Handle the settings form.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postSettings(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'email' => 'required|email|unique:users,email,'.$user->id,
            'public_key' => 'required|min:271',
        ]);

        $user->email = trim($request->email);
        $user->public_key = e(trim($request->public_key));
        $user->save();

        return redirect()->back()->with('saved', true);
    }
}

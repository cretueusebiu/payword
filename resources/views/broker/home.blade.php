@extends('broker.layouts.app')

@section('content')
<div class="container spark-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Account Balance</div>

                <div class="panel-body">
                    <p>Balance: <b>${{ Auth::user()->balanceInDollars() }}</b></p>
                    <p>Blocked: <b>${{ Auth::user()->blockedBalanceInDollars() }}</b></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('broker.layouts.app')

@section('content')
<div class="container spark-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Account Balance</div>

                <div class="panel-body">
                    <p>You have <b>${{ Auth::user()->balanceInDollars() }}</b>. and <em>${{ Auth::user()->blockedBalanceInDollars() }}</em> blocked.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

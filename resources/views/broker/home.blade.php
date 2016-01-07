@extends('broker.layouts.app')

@section('content')
<div class="container spark-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Account Balance</div>

                <div class="panel-body">
                    You have ${{ Auth::user()->balanceInDollars() }}.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('broker.layouts.app')

@section('content')
<div class="container spark-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Account Balance</div>

                <div class="panel-body">
                    <p>Balance: <b>${{ Auth::user()->balanceInDollars() }}</b></p>
                    @if (Auth::user()->isVendor())
                        <form method="POST" action="{{ url('/redeem') }}">
                            {!! csrf_field() !!}

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-credit-card fa-btn"></i> Redeem Money
                                </button>
                            </div>
                        </form>
                    @else
                        <p>Blocked: <b>${{ Auth::user()->blockedBalanceInDollars() }}</b></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

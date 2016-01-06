@extends('broker.layouts.app')

@section('content')
<div class="container spark-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Account Settings</div>

                <div class="panel-body">
                    <form method="POST" action="{{ url('/settings') }}">
                        {!! csrf_field() !!}

                        @if (Session::has('saved'))
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                Your changes have been saved.
                            </div>
                        @endif

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email">Email</label>

                            <input type="email" class="form-control" name="email" id="email" value="{{ Auth::user()->email }}">

                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('public_key') ? ' has-error' : '' }}">
                            <label for="public_key">Public Key</label>

                            <textarea type="text" class="form-control" name="public_key" id="public_key" rows="6">{{ Auth::user()->public_key }}</textarea>

                            @if ($errors->has('public_key'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('public_key') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-btn fa-save"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.email')

@section('subject')
    {{ $subject }}
@endsection

@section('content')

    {{ __('messages.password-reset-instructions') }} <br><br>

    <a href="{{env('INTRANET_URL')}}/session/resetPassword/{{$user->recovery_key}}">
        {{env('INTRANET_URL')}}/session/resetPassword/{{$user->recovery_key}}
    </a>

@endsection


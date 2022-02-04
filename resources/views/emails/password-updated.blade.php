@extends('layouts.email')

@section('subject')
    {{ $subject }}
@endsection

@section('content')
    {{ __('messages.password-updated') }}
@endsection
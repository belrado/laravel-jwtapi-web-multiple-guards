@extends('layouts.default')

@section('pageTitle')
    <x-page-title :page-title="$pageTitle" />
@endsection

@section('content')
    <div class="container">
        no:: {{$no}}
    </div>
@endsection

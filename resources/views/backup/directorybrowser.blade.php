@extends('layout.master')


@section('content')


<div class="container">

	<hr>

	@include('backup.subviews.menu')

	<hr>

	<iframe src="/{{ config('backup.apache_dir') }}/{{ config('backup.softlink_dir') }}/{{ $backup }}" width="100%" height="1000" style="border: none;"></iframe>

</div>

@endsection

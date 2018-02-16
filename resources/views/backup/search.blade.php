@extends('layout.master')


@section('content')

<div class="container">

	<hr>

	@include('backup.subviews.menu')

	<br>

	@if(sizeof($courseData) > 0)
		<h3>Suchergebnisse für {{ request('type') }} "{{ request('search') }}"</h3>
		<hr>
	@else
		<h3 class="center">Keine Suchergebnisse für {{ request('type') }} "{{ request('search') }}"</h3>
	@endif

	@include('backup.subviews.courses')

</div>

@endsection
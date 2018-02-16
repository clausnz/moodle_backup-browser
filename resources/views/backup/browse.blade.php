@extends('layout.master')


@section('content')


<div class="container">

	<hr>

	@include('backup.subviews.breadcrumbs')

	@include('backup.subviews.menu')

	<hr>

	@include('backup.subviews.categories')

	@include('backup.subviews.courses')

</div>

@endsection
@extends('layout.master')


@section('content')


<div class="container">

	<hr>

	<p>Backup Verzeichnis ausw&auml;hlen:</p>

	<br/>

	<table class="table table-hover table-striped table-start">
		<thead>
			<tr>
				<th>Backup</th>
				<th>&Auml;nderungsdatum</th>
			</tr>
		</thead>
		<tbody>
		@foreach($dirs as $dir)
			<tr onclick="location.href='/{{ config('backup.apache_dir') }}/browse/{{ basename($dir) }}'">
				<td>{{ basename($dir) }}</td>
				<td>{{ date("d.m.Y", filectime($dir)) }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>

</div>

@endsection
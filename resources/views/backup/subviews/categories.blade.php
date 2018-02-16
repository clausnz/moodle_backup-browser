@if(sizeof($categories) > 0)
	<table class="table table-striped table-hover table-category">
		<thead>
			<tr>
				<th>Kategorie-ID</th>
				<th>Kategoriename</th>
				<th>&Auml;nderungsdatum</th>
				<th>Sichtbar</th>
			</tr>
		</thead>
		<tbody>
			@foreach($categories as $category)
			<tr onclick="location.href='/{{ config('backup.apache_dir') }}/browse/{{ $backup }}/{{ $category->id }}'">
				<td>{{ $category->id }}</td>
				<td>{{ $category->name }}</td>
				<td>{{ strftime("%d.%m.%G", $category->timemodified) }}</td>
				<td><span class="glyphicon glyphicon-{{ $category->visible == 1 ? 'ok' : 'remove' }}" aria-hidden="true"></span></td>
			</tr>
			@endforeach
		</tbody>
	</table>
@endif
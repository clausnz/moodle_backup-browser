@if(sizeof($courseData) > 0)
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Kurs-ID</th>
				<th>Kursname</th>
				<th>Dozent(en)</th>
				<th>Dateigr&ouml;&szlig;e</th>
				<th>&Auml;nderungsdatum</th>
				<th>Sichtbar</th>
				<th>Download</th>
			</tr>
		</thead>
		<tbody>
		@foreach($courseData as $course)
			<tr>
				<td>{{ $course['id'] }}</td>
				<td>{{ $course['name'] }}</td>
				<td>{{ $course['teachername'] }}</td>
				<td>{{ $course['filesize'] }} MB</td>
				<td>{{ $course['formateddate'] }}</td>
				<td><span class="glyphicon glyphicon-{{ $course['visible'] == 1 ? 'ok' : 'remove' }}" aria-hidden="true"></span></td>
				<td>
				@if($course['filename'])
					@foreach($course['filename'] as $filename)
					<a href="/{{ config('backup.apache_dir') }}/{{ config('backup.softlink_dir') }}/{{ $backup }}/{{ explode('/', $filename)[sizeof(explode('/', $filename))-1] }}">
						<span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
					</a><br>
					@endforeach
				@elseif(!$course['filename'])
					<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
				@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
@endif
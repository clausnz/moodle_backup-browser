<div class="row">
		<div class="col-lg-3">

		<div class="btn-group" role="group" aria-label="...">
				<a class="btn btn-default" href="/{{ config('backup.apache_dir') }}"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a>
				<a class="btn btn-default" href="#" onclick="window.history.go(-1); return false;"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></a>

		  	<div class="btn-group" role="group">
		    	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		      Backup ausw&auml;hlen
		      		<span class="caret"></span>
		    	</button>
		    	<ul class="dropdown-menu">
		    		@foreach($dirs as $dir)
		      		<li><a href="/{{ config('backup.apache_dir') }}/browse/{{ basename($dir) }}">{{ basename($dir) }}</a></li>
		      		@endforeach
		    	</ul>
		  	</div>

		</div>
	</div>

	@if(!isset($hideSearch))

	<form action="/{{ config('backup.apache_dir') }}/search/{{ $backup }}" method="post">
		{{ csrf_field() }}
	  	<div class="col-lg-9">
	  		<div class="input-group">
	      		<input type="text" name="search" class="form-control" placeholder="Suchen nach...">
	  			<span class="input-group-btn">

					<select class="selectpicker" name="type">
							<option>{{ config('backup.select_coursename') }}</option>
							<option>{{ config('backup.select_teacher') }}</option>
					</select>

	    			<button class="btn btn-default" type="submit">Suche starten</button>
	  			</span>
    		</div>
    	</div>
	</form>

	@endif

</div>
<ol class="breadcrumb">
	@foreach($breadcrumbs as $key => $bc)
		@if($key+1 !== sizeof($breadcrumbs))
			<li><a href="{{ $bc['link'] }}">{{ $bc['name'] }}</a></li>
		@else
			<li class="active">{{ $bc['name'] }}</li>
		@endif
	@endforeach
</ol>
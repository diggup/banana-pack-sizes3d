<div class="container resultsTable">
	<div class="table-responsive">
	   <table class="table">
		  <caption class="resultsCaption">{{ $data['num_bananas_required'] }} bananas</caption>
		  <thead>
			 <tr>
				<th>Pack Size</th>
				<th>Amount</th>
			 </tr>
		  </thead>
		  <tbody>
			@foreach($data['results'] as $k => $v)
			   <tr><td>{{ $k }}</td><td>{{ $v }}</td></tr>
			@endforeach
		  </tbody>
	   </table>
	</div>
</div>**@foreach($data['results'] as $k => $v){{ $k }}:{{ $v }},@endforeach
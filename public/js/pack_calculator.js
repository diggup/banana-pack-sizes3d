	$(document).ready(function() {
		
		var timeoutID = null;

		function ajaxCalcBananas (num) {
			
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('[name="_token"]').val()
				}
			});
			
			$.ajax({
				type: "POST",
				url: "/ajaxbanana",
				data: {'num_bananas' : '' + num + ''},
				cache: false,
				success: function(data) {
					res = data.split("**");
					packResults = [];
					if (res[1]) {
						packResults = res[1].split(',');
					}
					$("#displayResults").hide();
					$('#displayResults').html(res[0]);
					$('#displayResults').fadeIn();
					world.moveWheels(num, packResults);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					console.log(xhr.status);
					console.log(thrownError);
				}
			});
			
		}
		
		function doAjaxBananaCalc (e) {
		  clearTimeout(timeoutID);
		  timeoutID = setTimeout(() => ajaxCalcBananas(e.target.value), 100);
		}
		
		$('#num_bananas').keyup(function(e) {
			doAjaxBananaCalc(e);
		});
		
		$('#btnCalc').click(function() {
			$('#num_bananas').keyup();
		});

	});

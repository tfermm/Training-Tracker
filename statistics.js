
$(function() { 
		
		$( ".confirmButton" ).click(function() {
			$( "#dialog-confirm" ).dialog({
					resizable: false,
					height:240,
					modal: true,
					buttons: {
						"Yes": function() {
							$( this ).dialog( "close" );
								var postData = new Array();
								postData[0] = current_user_wpid;
								postData[1] = active_user_wpid;
								postData[2] = current_user_level;
								$.ajax({
									type: "POST",
									url: "/webapp/training-tracker/staff/checklist/confirm",
									data: { data: postData }
								});
								window.location = "/webapp/training-tracker/";//redirect back to the main page 
							},
							"No": function() {
								$( this ).dialog( "close" );
							}
					}
			});
		});

		$(".confirm").mouseenter(function() {
			 $(".confirm-text").addClass('tt-warning');
		 }).mouseleave(function() {
			 $(".confirm-text").removeClass('tt-warning');
		 })
		$( ".submit" ).click(function() {
			var postData = new Array();

			//get the contents of the checkbox
			postData[0] = $(".txtarea").val();
			//the checked checkboxes
			postData[1] = current_user_wpid;
			//append the value to the data base
			$.ajax({
				type: "POST",
					url: "/webapp/training-tracker/checklist/post/comments",
				data: { data: postData }
			});
			window.location = "/webapp/training-tracker/";//redirect back to the main page 
		});

		//as the coments box is modified send the values to the database
		$(".chkbox").on('click', $("div.chkbox input[type=checkbox]").is(":checked"),outputDataCheck);

		$(".progressbar").progressbar({});	
		var progressbars = $(".progressbar");
		$.each(progressbars, function(){
			var progress = $(this).data("progress");
			$(this).progressbar("option","value", progress);
		});
		var checkboxes = $(".chkbox-container");
		$.each(checkboxes, function(){
			 $(this).mouseenter(function() {
				 $(this).addClass('highlight');
			 }).mouseleave(function() {
				 $(this).removeClass('highlight');
			 });
			$(this).tooltip({
					placement: "right" 
			});
		});

		$("#accordion").accordion();
		$("#outer-accordion").accordion();
	});

	function outputDataCheck(e){

		var postData = new Array();

		//if it checkbox you clicked was just checked.
		if (e.target.checked){
			//pass complete
			var response = "complete"
		}else{
			//pass n/a	
			var response = "incomplete"
		}
		//active user is the person looking at the page
		//current user is the person they are looking at
		postData[0]=e.target.id; //id of the checkbox
		postData[1]=current_user_wpid;
		postData[2]=response;
		console.log(postData);
		$.ajax({
			type: "POST",
			url: "/webapp/training-tracker/staff/checklist/item",
			data: { data: postData }
		});	

	}

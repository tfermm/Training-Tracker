
$(document).ready(function() { 
		
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
								console.log(postData);
								$.ajax({
									type: "POST",
									url: "/webapp/training-tracker/checklist_post_confirm",
									data: { data: postData }
								});
				//				window.location = "/webapp/training-tracker/";//redirect back to the main page 
							},
							"No": function() {
								$( this ).dialog( "close" );
							}
					}
			});
		});
/*
		$( ".submitButton" ).click(function() {
			var postData = new Array();

			//get the contents of the checkbox
			postData[0] = $(".txtarea").val();
			console.log(postData[0]);
			//the checked checkboxes
			postData[1] = current_user_wpid;
			//append the value to the data base
			$.ajax({
				type: "POST",
					url: "/webapp/training-tracker/checklist_post_comments",
				data: { data: postData }
			});
			window.location = "/webapp/training-tracker/";//redirect back to the main page 
		}); */

		//as the coments box is modified send the values to the database
		$(".chkbox").on('click', $("div.chkbox input[type=checkbox]").is(":checked"),outputDataCheck);

		//split the checked string, which is the checked checkboxes stored by id,id,id... etc
		for (var i in checkboxData){
			$("#"+i).prop("checked", true);  //sets all previously checked checkboxes to checked.
		}

		$("#accordion").accordion();
		$("#outer-accordion").accordion();
		$(".progressbar").progressbar({});	
	});


	function outputData(e){

		var postData = new Array();
		//e.target.value is the contents of the comment box.
		postData[0]=e.target.value;
		postData[1]=current_user_wpid;
		$.ajax({
			type: "POST",
			url: "/webapp/training-tracker/checklist_post_comments",
			data: { data: postData }
		});

	}

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
		$.ajax({
			type: "POST",
			url: "/webapp/training-tracker/checklist_post_chkbox",
			data: { data: postData }
		});	

	}

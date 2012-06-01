
$(document).ready(function() { 
		
		$( ".confirmButton" ).button();
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
								url: "/webapp/training-tracker/checklist_post_confirm",
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

		$( ".submitButton" ).button();
		$( ".submitButton" ).click(function() {
			var postData = new Array();

			//get the contents of the checkbox
			postData[0] = $(".txtarea").val();
			//the checked checkboxes
			postData[1] = checkboxData;
			postData[2] = current_user_wpid;
			postData[3] = active_user_wpid;
			postData[4] = current_user_level;

			//append the valuse to the data base
			$.ajax({
				type: "POST",
				url: "/webapp/training-tracker/checklist_post_done",
				data: { data: postData }
			});
			window.location = "/webapp/training-tracker/";//redirect back to the main page 
		}); 

		//as the coments box is modified send the values to the database
		$(".chkbox").on('click', $("div.chkbox input[type=checkbox]").is(":checked"),outputDataCheck);
		$(".txtarea").on('keyup',outputData);

		//split the checked string, which is the checked checkboxes stored by id,id,id... etc
		var checked = checkboxData.split(",");
		for (var i in checked){
			$("#"+checked[i]).prop("checked", true);  //sets all previously checked checkboxes to checked.
		}

		$("#accordion").accordion();
		$("#outer-accordion").accordion();
		
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
		//building a string to store the checked checkboxes, the id's are seperated by a ","

		//if it checkbox you clicked was just checked.
		if (e.target.checked){
			if (checkboxData == "0"){
				//if there was no data in checkboxData before set it to the id of the checkbox
				checkboxData = e.target.id;
			}else{
				//if there is data in the checkboxData variable append ",id" to the end
				if (checkboxData.indexOf(e.target.id)==-1){
					checkboxData = checkboxData + "," + e.target.id;
				}
			}
		}else{

			//if you just unchecked the checkbox
			if (checkboxData.indexOf(e.target.id)!=-1){
					//if the checkbox id is in checkboxData
					if (checkboxData.indexOf(e.target.id) > 1){
						var replace_string = "," + e.target.id; 
						//replace the id and a , if it isnt the first thing in the string with ""
						checkboxData = checkboxData.replace(replace_string,"");
					}else{
						//if it is the first id in the string then replace it with ""
						var replace_string = e.target.id; 
						checkboxData = checkboxData.replace(replace_string,"");
						
					}
				}


		}
		//checking the string for extranious cases.
		if (checkboxData.indexOf(",")==0){
			checkboxData = checkboxData.replace(",","");
		}
		if (checkboxData == ""){
				checkboxData = "0";
		}
		//active user is the person looking at the page
		//current user is the person they are looking at

		postData[0]=checkboxData;
		postData[1]=current_user_wpid;
		postData[2]=active_user_wpid;
		$.ajax({
			type: "POST",
			url: "/webapp/training-tracker/checklist_post_chkbox",
			data: { data: postData }
		});	

	}

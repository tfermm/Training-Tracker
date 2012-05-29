
$(document).ready(function() { 
		
		$( ".confirmButton" ).button();

		$( ".submitButton" ).button();
		$( ".submitButton" ).click(function() {
			var postData = new Array();
			postData[0] = $(".txtarea").val();
			postData[1] = checkboxData;
			postData[2] = current_user_wpid;
			postData[3] = active_user_wpid;
			postData[4] = current_user_level;
			$.ajax({
				type: "POST",
				url: "/webapp/training-tracker/checklist_post_done",
				data: { data: postData }
			});
			window.location = "/webapp/training-tracker/";//redirect back to the main page 
		}); 

		$( "#team_builder" ).button();
		$( "#team_builder" ).click(function() { window.location = "/webapp/training-tracker/teams"; });

		$( "#view_teams" ).button();
		$( "#view_teams" ).click(function() { window.location = "/webapp/training-tracker/viewteams"; });

		$( "#cklist" ).button();
		$( "#cklist" ).click(function() { window.location = "/webapp/training-tracker/"; });

		$(".chkbox").on('click', $("div.chkbox input[type=checkbox]").is(":checked"),outputDataCheck);
		$(".txtarea").on('keyup',outputData);

		//checkboxData = "{$checked}"; //set the string that has the ids of the checkboxes to a javascript variable

		var checked = checkboxData.split(",");
		for (var i in checked){
			$("#"+checked[i]).prop("checked", true);  //sets all previously checked checkboxes to checked.
			console.log(checked[i]);
		}

		$("#accordion").accordion();
		$("#outer-accordion").accordion();

	});


	function outputData(e){

		var postData = new Array();
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
		if (e.target.checked){
			if (checkboxData == "0"){
				checkboxData = e.target.id;
			}else{
				if (checkboxData.indexOf(e.target.id)==-1){
					checkboxData = checkboxData + "," + e.target.id;
				}
			}
		}else{
			if (checkboxData.indexOf(e.target.id)!=-1){
					if (checkboxData.indexOf(e.target.id) > 1){
						var replace_string = "," + e.target.id; 
						checkboxData = checkboxData.replace(replace_string,"");
					}else{
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

		console.log(checkboxData.match("/k[2,]/"));
		console.log(checkboxData);
		postData[0]=checkboxData;
		postData[1]=current_user_wpid;
		postData[2]=active_user_wpid;
		$.ajax({
			type: "POST",
			url: "/webapp/training-tracker/checklist_post_chkbox",
			data: { data: postData }
		});	

	}

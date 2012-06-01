$(function() {
		
		$( "#mentee, " + mentor_string ).sortable().disableSelection();

		var $tabs = $( "#tabs" ).tabs();
		var $tab_items = $( "ul:first li", $tabs ).droppable({
			

			accept: ".connected-sortable li",
			hoverClass: "ui-state-hover",
			over: function(event, ui) {
				var $item = $(this);
				var $list = $($item.find("a").attr("href")).find(".connected-sortable");
				$tabs.tabs("select", $tab_items.index($item));
				ui.draggable.appendTo($list).show("slow");
			},
			drop: function( event, ui ) {
				var $item = $( this );
				var $list = $( $item.find( "a" ).attr( "href" ) )
					.find( ".connected-sortable" );

				ui.draggable.hide( "slow", function() {
					$tabs.tabs( "select", $tab_items.index( $item ) );
					$( this ).appendTo( $list ).show( "slow" );
					//add ajax request to move this person in the db
					//jQuery.post()
					//work on getting the ids out of this and $item
				
					//TODO: add mouse over event to load tab
					
					//var uid = $item.find();


					var postData = new Array();

					console.log(this.id);//id for mentee
					postData[0] = this.id;
					console.log($item.find('a').attr('id'));//the id for mentor
					postData[1] = $item.find('a').attr('id');
					console.log(this); //has the mentee
					console.log($item); //has the mentor
					
					var mentorId = $item.find('a').attr('id'); 
					console.log ( postData );
					$.ajax({
					  type: "POST",
						url: "teams_post",
						data: { data: postData }
					});
			});
			}
		});
	}); 				

/*
$(document).ready(function() { 
		
		$( ".confirmButton" ).button();

		$( ".submitButton" ).button();
		$( ".submitButton" ).click(function() {
			var postData = new Array();
			postData[0] = $(".txtarea").val();
			postData[1] = checkboxData;
			postData[2] = "{$current_user.wpid}";
			postData[3] = "{$active_user.wpid}";
			postData[4] = "{$current_user_level}";
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

		checkboxData = "{$checked}"; //set the string that has the ids of the checkboxes to a javascript variable

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
		postData[1]="{$current_user.wpid}";
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
		postData[1]="{$current_user.wpid}";
		postData[2]="{$active_user.wpid}";
		$.ajax({
			type: "POST",
			url: "/webapp/training-tracker/checklist_post_chkbox",
			data: { data: postData }
		});	

	} */

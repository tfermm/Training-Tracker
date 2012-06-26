	$(function(){
		$(".chzn-select").chosen(); 
		$(document).on( 'change', 'select.list1', function() { 
			$("#team1").empty();
			for(var i in teams[$(this).val()]){
				$("#team1").append('<li class="ui-state-default" id="' + i + '">' + teams[$(this).val()][i]['name'] + '</li>');
			}	
		});

		$(document).on( 'change', 'select.list2', function() { 
			$("#team2").empty();
			for(var i in teams[$(this).val()]){
				$("#team2").append('<li class="ui-state-default" id="' + i + '">' + teams[$(this).val()][i]['name'] + '</li>');
			}
		});

		$( "#team1, #team2" ).sortable({
			connectWith: ".connectedSortable",
			appendTo: 'body',
			helper: function(event,$item){
				console.log(event);
				var $helper = $('<ul class = "styled"><li id="' +  event.originalEvent.target.id + '">' + event.originalEvent.target.innerHTML + '</li></ul>');
				mentee_wpid = event.originalEvent.target.id;
				mentor_wpid_alt = $("#"+event.currentTarget.id).parent().find("select").val();

				return $helper;
			},
			receive: function(event, ui){
				//the number of the team it is dropping to is the last character, so this grabs the last character
				var mentor_wpid = $(this).parent().find("select").val();

				//teams[mentor_wpid] //+= teams[mentor_wpid_alt][mentee_wpid]);
				if (_.isUndefined(teams[mentor_wpid]) != true){

					if (mentor_wpid != mentor_wpid_alt && mentor_wpid.length != 0){
						//using jquery for a deep copy
						teams[mentor_wpid][mentee_wpid] = $.extend(true, {}, teams[mentor_wpid_alt][mentee_wpid]);
						delete teams[mentor_wpid_alt][mentee_wpid];
						changeTeam(mentor_wpid, mentee_wpid);
						//call ajax
					}
				}
				else{
					if (mentor_wpid != mentor_wpid_alt && mentor_wpid.length != 0){
						teams[mentor_wpid] = { };
						teams[mentor_wpid][mentee_wpid] = $.extend(true, {}, teams[mentor_wpid_alt][mentee_wpid]);
						delete teams[mentor_wpid_alt][mentee_wpid];
						changeTeam(mentor_wpid, mentee_wpid);
						//call ajax
					}
				}
			}
		}).disableSelection();
		function changeTeam(mentor_wpid, mentee_wpid){
			postData = new Array();
			postData[0] = mentee_wpid;
			postData[1] = mentor_wpid;
			$.ajax({
				type: "POST",
				url: "builder",
				data: { data: postData }
			});
		}
	});


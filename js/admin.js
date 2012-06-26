$(function () {
	var $people = $(".person");
	$.each($people, function(){
		var permission = $(this).find('.permission').html();
		if (permission == 'Information Desk Trainee'){
				$(this).find(".demote").attr("disabled", "disabled");
		}
		else if (permission == 'Senior Information Desk Consultant'){
				$(this).find(".promote").attr("disabled", "disabled");
		}
	});
	$(".demote").on('click', function(){
		var wpid = $(this).data("wpid");
		demotionPost(wpid);
	});

	$(".promote").on('click', function(){
		var wpid = $(this).data("wpid");
		promotionPost(wpid);
	});	
});

function demotionPost(wpid){
		var name = $("#person_" + wpid).find(".name").html();
		$(".popup_text").text("Are sure you want to demote " + name + "?");
		$( "#confirmation" ).dialog({
					resizable: false,
					height:200,
					modal: true,
					buttons: {
						"Yes": function() {
							var demoteText = $("#person_" + wpid).find(".permission").html();
							if (demoteText == "Senior Information Desk Consultant"){
								var demoteTo = "sta";
								var demoteName = "Information Desk Consultant";
								$("#person_" + wpid).find(".promote").removeAttr("disabled");
							}
							else{
								var demoteTo = "trainee";
								var demoteName = "Information Desk Trainee";

								$("#person_" + wpid).find(".demote").attr("disabled", "disabled");
								$("#person_" + wpid).find(".promote").removeAttr("disabled");
							}
							$("#person_" + wpid).find(".permission").text(demoteName);
							var postData = Array();
							postData[0] = demoteTo;
							postData[1] = wpid;
							$.ajax({
									type: "POST",
									url: "/webapp/training-tracker/staff/fate",
									data: { data: postData }
							}); 
							$.gritter.add({
								title: "You just demoted " + name,
								text: name + " was just demoted to a " + demoteName + ".",
							});
							$( this ).dialog( "close" );
						},
						"No": function() {
							$( this ).dialog( "close" );
						}
				}
		});
	}
	function promotionPost(wpid){
		var name = $("#person_" + wpid).find(".name").html();
		$(".popup_text").text("Are sure you want to promote " + name + "?");
		$( "#confirmation" ).dialog({
					resizable: false,
					height:200,
					modal: true,
					buttons: {
						"Yes": function() {
							var promoteText = $("#person_" + wpid).find(".permission").html();
							if (promoteText == "Information Desk Trainee"){
								var promoteTo = "sta";
								var promoteName = "Information Desk Consultant";
								$("#person_" + wpid).find(".demote").removeAttr("disabled");
							}
							else{
								var promoteTo = "shift_leader";
								var promoteName = "Senior Information Desk Consultant";
								$("#person_" + wpid).find(".promote").attr("disabled", "disabled");
								$("#person_" + wpid).find(".demote").removeAttr("disabled");
							}
							$("#person_" + wpid).find(".permission").text(promoteName);
							var postData = Array();
							postData[0] = promoteTo;
							postData[1] = wpid;
							$.ajax({
								type: "POST",
								url: "/webapp/training-tracker/staff/fate",
								data: { data: postData }
							});  
							$( this ).dialog( "close" );
							$.gritter.add({
								title: "You just promoted " + name,
								text: name + " was just promoted to a " + promoteName + ".",
							});
					},
						"No": function() {
							$( this ).dialog( "close" );
						}
					}
			});
	}

$(function() { 

	//as the coments box is modified send the values to the database
	$(".chkbox").on('click', $("div.chkbox input[type=checkbox]").is(":checked"),outputDataCheck);

	$(".confirm").mouseenter(function(){
		$(".confirm-text").addClass("tt-warning");
	}).mouseleave(function(){
		$(".confirm-text").removeClass("tt-warning");
	});	

	$("#accordion").accordion();
	$("#outer-accordion").accordion();
	$(".progressbar").progressbar();	
	var progressBars = $(".progressbar");
	$.each(progressBars, function(){
		$(this).progressbar( "option", "value", $(this).data('progress'));
	});
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
	$.ajax({
		type: "POST",
		url: "/webapp/training-tracker/staff/checklist/item",
		data: { data: postData }
	});	

}

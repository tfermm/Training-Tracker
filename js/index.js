$(function(){
	$(".progressbar").progressbar({});

	var $progressbars = $(".progressbar");

	$.each($progressbars, function(){
		var value = $(this).data("progress");
		var id = $(this).attr("id");
		$("#"+id).progressbar("option","value", value );
	});

});

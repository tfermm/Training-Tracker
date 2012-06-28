$(function() { 

		$('.confirm').mouseenter(function(){
		$('.confirm-text').addClass('tt-warning');
	}).mouseleave(function(){
		$('.confirm-text').removeClass('tt-warning');
	});	

	var $progress = $('.progressbar');
	$('#goals').accordion();
	$progress.progressbar();	

	$progress.each( function(){
		$(this).progressbar( 'option', 'value', $(this).data('progress'));
	});
});


//as the coments box is modified send the values to the database
$(document).on('click', '.chkbox', TrainingTracker.outputDataCheck);

$(document).on('click', '.chkbox', TrainingTracker.recaculateProgress);


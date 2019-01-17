$(document).ready(function(){
	hljs.initHighlightingOnLoad();
	
	$('.stack').on('click', function(){
		var ref = $(this).attr('ref');
		$('.selected').removeClass('selected');
		$('[ref=' + ref + ']').addClass('selected');
	});
});
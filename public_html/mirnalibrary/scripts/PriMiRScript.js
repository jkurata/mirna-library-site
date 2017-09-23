$(document).ready(function(){
	$('#sgRNA-list').tablesorter({
		theme: 'bootstrap', 
		widthFixed: true, 
		headerTemplate: '{content} {icon}',
		widgets:['uitheme']});
	$('.sg-check').click(function(){
		var id = $(this).attr('id'); 
		var color = ['red','orange','green','blue', 'purple','brown','salmon'];
		if($(this).is(':checked')){
			var numActive = $('.sg-check:checked').size();
			$('div.miR-diagram > .'+id).css('color', color[numActive-1]);
			//$('div.miR-diagram > .'+id+'.cleave'+id).prepend("^");
			//$("^").prepend('.'+id+'.cleave'+id);
		}else{
			$('div.miR-diagram > .'+id).removeAttr('style');
		}
	});
});
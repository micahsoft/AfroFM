$(document).ready(function(){
	var url = $('#buynow').data('url');
	var params = 'url='+url;
	var buynow = $('#buynow');
	
	$.post(website_url + '?ajax=on&l=itunes.button', params, function(data) {
		buynow.html(data).fadeIn("slow");
	});
});

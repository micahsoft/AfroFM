$(function () {
	
	// Preload images
	$.preloadCssImages();
	
	$('#search').live('submit', function () {
		var loc = $(this).attr('action');
        var params = $(this).serialize();
        redirect(loc, params);
        return false;
    });
	
	$('a.toggleSearch').live('click', function() {
		
		searchDiv = $(this).parent().parent().parent().parent().find('.block_subhead');

		if($(searchDiv).css('display') == 'none') {
			$(searchDiv).slideDown();
			$(this).parent().addClass('active');
		}else{
			$(searchDiv).slideUp();
			$(this).parent().removeClass('active');
		}
		
	});
	
	$('#manage').live('submit', function () {

		var obj = $(this);
		var action = obj.find('select').val();		
		var items = obj.data('items');
		var url = obj.data('url');
		var params = obj.serialize();
		var selected = obj.find('.selected:checked');
		var count = selected.length;

		if(count == 0) {
			alert('Please select at least 1 row!');
			return false;
		}else if(action == '') {
			alert('Please select an action!');
			return false;
		}
		
		var answer = confirm("Are you sure you want to "+action+" these "+items+"?");
		if (answer){
			selected.each(function() {
				thisParent = $(this).parent().parent();
				thisParent.find('.action .ajax.'+action).data('force', 'true').trigger('click');
			});				

		}
		return false;
	});

	$('.action .ajax').live('click',function() {

		var obj = $(this);
		var action = obj.data('action');
		var item = obj.data('item');
		var url = obj.attr('href');
		var params = obj.data('params');
		var force = obj.data('force');
		var filePath = obj.data('filepath');
		var targetImage = obj.data('targetimage');

		if(force)
			var answer = true;
		else	
			var answer = confirm("Are you sure you want to "+action+" this "+item+"?");
			
		if (answer){				
			$.post(url, params, function(data) {
				if(data == 'error') return false;
				
				if(item == 'picture' && targetImage && filePath) {
					$(targetImage).attr('src', filePath+data);
				}else if(item == 'genre') {
					if(action == 'feature') {
						obj.parent().find('span').text('Yes');
						obj.parent().removeClass('red').addClass('green');
						obj.parent().find('a.ajax.unfeature').show();
						obj.parent().find('a.ajax.feature').hide();
						
					}else if(action == 'unfeature') {
						obj.parent().find('span').text('No');
						obj.parent().removeClass('green').addClass('red');
						obj.parent().find('a.ajax.unfeature').hide();
						obj.parent().find('a.ajax.feature').show();
					}else if(action == 'hide') {
						obj.parent().find('span').text('Yes');
						obj.parent().removeClass('red').addClass('green');
						obj.parent().find('a.ajax.hide').hide();
						obj.parent().find('a.ajax.show').show();
					}else if(action == 'show') {
						obj.parent().find('span').text('No');
						obj.parent().removeClass('green').addClass('red');
						obj.parent().find('a.ajax.hide').show();
						obj.parent().find('a.ajax.show').hide();
					}
											
					subgenresLink = obj.parent().parent().find('.show-subgenres');
					if(subgenresLink.length > 0) {
						target = '#sub_' + subgenresLink.data('pid');
						$(target).find('.ajax.'+action).each(function() {
						
							$(this).trigger('click');
						});
					}
						
				}else{
					obj.parent().parent().fadeOut('slow');
				}
			});
		}
		return false;
	});
	
	$('.show-subgenres').click(function(e) {
		e.preventDefault();
		target = '#sub_' + $(this).data("pid");
		
		if($(target).css('display') == 'none') {
			$(target).show();
			$(target).prev().find('td').css({'border-top':'1px solid #000000','border-bottom':'1px solid #000000'});
		}else{
			$(target).hide();
			$(target).prev().find('td').css({'border-top': 'none', 'border-bottom': '1px solid #DDD'});
		}
	
	});

	$('#settingsGroupMenu').change(function() {
		var value = $(this).val();
		var form = $('form#search'); 
		form.find('input[name=group]').val(value);
		form.submit();
		
	});
		
	// CSS tweaks
	$('#header #nav li:last').addClass('nobg');
	$('.block_head ul').each(function() { $('li:first', this).addClass('nobg'); });
	$('.block form input[type=file]').addClass('file');
			
			
	
	// Web stats
	$('table.stats').each(function() {
		
		if($(this).attr('rel')) {
			var statsType = $(this).attr('rel');
		} else {
			var statsType = 'area';
		}
		
		var chart_width = ($(this).parent('div').width()) - 60;
		
				
		if(statsType == 'line' || statsType == 'pie') {		
			$(this).hide().visualize({
				type: statsType,	// 'bar', 'area', 'pie', 'line'
				width: chart_width,
				height: '240px',
				colors: ['#6fb9e8', '#ec8526', '#9dc453', '#ddd74c'],
				
				lineDots: 'double',
				interaction: true,
				multiHover: 5,
				tooltip: true,
				tooltiphtml: function(data) {
					var html ='';
					for(var i=0; i<data.point.length; i++){
						html += '<p class="chart_tooltip"><strong>'+data.point[i].value+'</strong> '+data.point[i].yLabels[0]+'</p>';
					}	
					return html;
				}
			});
		} else {
			$(this).hide().visualize({
				type: statsType,	// 'bar', 'area', 'pie', 'line'
				width: chart_width,
				height: '240px',
				colors: ['#6fb9e8', '#ec8526', '#9dc453', '#ddd74c']
			});
		}
	});
	
	
	
	// Sort table
	$("table.sortable").tablesorter({
		headers: { 0: { sorter: false}, 5: {sorter: false} },		// Disabled on the 1st and 6th columns
		widgets: ['zebra']
	});
	
	$('.block table tr th.header').css('cursor', 'pointer');
		
	
	
	// Check / uncheck all checkboxes
	$('.check_all').click(function() {
		$(this).parents('form').find('input:checkbox[disabled=false]').attr('checked', $(this).is(':checked'));   
	});
		
	
	
	// Set WYSIWYG editor
	$('.wysiwyg').wysiwyg({css: "css/wysiwyg.css", brIE: false });
	
	
	
	// Modal boxes - to all links with rel="facebox"
	$('a[rel*=facebox]').facebox({
		opacity: 0.8,
		loadingImage : theme_base+'images/loading.gif',
      	closeImage   : theme_base+'images/closelabel.gif'
	});
	
	
	
	// Messages
	$('.block .message').hide().append('<span class="close" title="Dismiss"></span>').fadeIn('slow');
	$('.block .message .close').hover(
		function() { $(this).addClass('hover'); },
		function() { $(this).removeClass('hover'); }
	);
		
	$('.block .message .close').click(function() {
		$(this).parent().fadeOut('slow', function() { $(this).remove(); });
	});
	
	
	
	// Form select styling
	$("form select.styled").select_skin();
	
	
	
	// Tabs
	$(".tab_content").hide();
	$("ul.tabs li:first-child").addClass("active").show();
	$(".block").find(".tab_content:first").show();

	$("ul.tabs li").click(function() {
		$(this).parent().find('li').removeClass("active");
		$(this).addClass("active");
		$(this).parents('.block').find(".tab_content").hide();
			
		var activeTab = $(this).find("a").attr("href");
		$(activeTab).show();
		
		// refresh visualize for IE
		$(activeTab).find('.visualize').trigger('visualizeRefresh');
		
		return false;
	});
	
	
	
	// Sidebar Tabs
	$(".sidebar_content").hide();
	
	if(window.location.hash && window.location.hash.match('sb')) {
	
		$("ul.sidemenu li a[href="+window.location.hash+"]").parent().addClass("active").show();
		$(".block .sidebar_content#"+window.location.hash).show();
	} else {
	
		$("ul.sidemenu li:first-child").addClass("active").show();
		$(".block .sidebar_content:first").show();
	}

	$("ul.sidemenu li").click(function() {
	
		var activeTab = $(this).find("a").attr("href");
		window.location.hash = activeTab;
	
		$(this).parent().find('li').removeClass("active");
		$(this).addClass("active");
		$(this).parents('.block').find(".sidebar_content").hide();			
		$(activeTab).show();
		return false;
	});	
	
	
	
	// Block search
	$('.block .block_head form .text').bind('click', function() { $(this).attr('value', ''); });
	
	
	
	// Image actions menu
	$('ul.imglist li').hover(
		function() { $(this).find('ul').css('display', 'none').fadeIn('fast').css('display', 'block'); },
		function() { $(this).find('ul').fadeOut(100); }
	);
	
	
		
	// Image delete confirmation
	$('ul.imglist .delete a').click(function() {
		if (confirm("Are you sure you want to delete this image?")) {
			return true;
		} else {
			return false;
		}
	});
	
	
	
	// Style file input
	$("input[type=file]").filestyle({ 
	    image: theme_base+"images/upload.gif",
	    imageheight : 30,
	    imagewidth : 80,
	    width : 136
	});
	
	
	
	// File upload
	if ($('#fileupload').length) {
		var obj = $('#fileupload');
		var action = obj.data('action');
		var params = obj.data('params');
		var filePath = obj.data('filepath');
		var targetImage = obj.data('targetimage');
		params = params.split('&');
		data = new Array();
		for(i = 0 ; i < params.length ; i++) {
			param = params[i].split('=');
			key = param[0];
			val = param[1];
			data[key] = val;
		}
		var name = obj.attr('name');

		new AjaxUpload('fileupload', {
			action: action,
			data: data,
			autoSubmit: true,
			name: name,
			responseType: 'text/html',
			onSubmit : function(file , ext) {
					$('.fileupload #uploadmsg').addClass('loading').text('Uploading...');
					this.disable();	
			},
			onComplete : function(file, response) {

				if(targetImage && filePath && response != 'error') {
					$(targetImage).attr('src', filePath+response);
					msg = 'File uploaded successfully';
				}else{
					msg = 'Error while uploading file';
				}	
				$('.fileupload #uploadmsg').removeClass('loading').text(msg);
				
				this.enable();
			}	
		});
	}
		
		
	
	// Date picker
	$('input.date_picker').date_input();
	
	if($("#groups").length > 0) {
		$("#groups").autocomplete({
		
			source: website_url+'?ajax=on&l=settings&action=completegroups',
			minLength: 2,
			select: function( event, ui ) {
				
				console.log( ui.item ?
					"Selected: " + ui.item.value + " aka " + ui.item.id :
					"Nothing selected, input was " + this.value );
			}
		});
	}	
	// Navigation dropdown fix for IE6
	if(jQuery.browser.version.substr(0,1) < 7) {
		$('#header #nav li').hover(
			function() { $(this).addClass('iehover'); },
			function() { $(this).removeClass('iehover'); }
		);
	}

	// IE6 PNG fix
	$(document).pngFix();
		
});


function redirect(l, params, t) {
    if (t != undefined) {
    	trail = t;
    }else{
    	 trail = "";
   	}
   	if (params == undefined) {
    	params = "";
    }
    if (seo_enabled) {
        l = seoURL(l);
        //params = decodeURIComponent(params);
        seoparams = seoParams(params);
        location.href = website_url + l + seoparams + trail;
    } else {
        if (params != "") params = "&"+params;
       	location.href = website_url + '?l=' + l + params;
    }
}

function link(l, q, t) {
    if (t != undefined) trail = t;
    else trail = "";
    if (seo_enabled) {
        retLoc = seoURL(l);
        retParams = seoParams(q);
        return website_url + retLoc + retParams + trail;
    } else {
        if (q && q != "") query = "&" + q;
        else query = "";
        return website_url + "?l=" + l + query;
    }
}

function seoURL(l) {
    if (l == "") return "";
    retLoc = l.replace(".", "_");
    retLoc += "/";
    return retLoc;
}

function seoParams(params) {
    if (params && params != "") {
        params = params.replace(/&/gi, "/");
        params = params.replace(/=/gi, ":");
        params += "/";
        return params;
    } else {
        return "";
    }
}

function preg_match_all(regex, content) {
    var globalRegex = new RegExp(regex, 'g');
    var matches = content.match(globalRegex);
    return matches;
}
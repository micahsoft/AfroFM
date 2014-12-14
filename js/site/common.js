jQuery.fn.exists = function () {

    return this.length > 0;

}

jQuery.fn.maxZIndex = function(opt) {

    var def = { inc: 10, group: "*" };

    $.extend(def, opt);    

    var zmax = 0;

    $(def.group).each(function() {

        var cur = parseInt($(this).css('z-index'));

        zmax = cur > zmax ? cur : zmax;

    });

    if (!this.jquery)

        return zmax;



    return this.each(function() {

        zmax += def.inc;

        $(this).css("z-index", zmax);

    });

}



var default_playlist_text = 'Enter playlist name...';

var playlistBoxIsOpen = false;

var qtipIsOpen = false;

var shuffleEnabled = false;

var videoEnabled = false;

var shuffleIndex = 0;

var shuffleData = {}

var videoHeight = 400;

var qtipVideoHeight = 200;

var trackHeight = 24;

var playerWrapSelector = '.player_wrap';

var playerSelector = '.player';

var grabInterval;

var trackdata = [];

var $currentTrack;

var $currentSelection;

var $trackContainer;

var $trackPlayer;

var $trackPlayerWrap;

var ytPlayer = false;

var plAjaxHandler;



$(window).load(function () {

    setTimeout(function() {

    	stopLoading('#pageLoading');

    },500);

    

});



$(document).ready(function () {



	if (location.hash) {

		setTimeout(function() {

	    	hash = location.hash;

	    	if(hash.search(/#play\/tid\//i) != -1) {

				$('.vgrid .items').fadeIn();

	            hash = hash.split('#play/tid/');

	            trackId = hash[1];

				$('#track_row_'+trackId+' .play').trigger('click');

	        }

		},700);

	}



    $('body').fadeIn(500);

    

    $('#search').focus();



	prevUrl = '';

	$('a.lite').click(function() {

	

		$('#lightsOff').fadeIn();

		startLoading('#pageLoading');

		url = $(this).attr('href');

		if($(this).data('formAction'))

			formAction = $(this).data('formAction');

			

		$('#lite-page').css('top','-1000px').show();

		

		if(url != prevUrl) {

			$.get(url, function(data) {

				close = '<a href="#" class="ui-corner-top close">close</a>';

				$('#lite-page').html(close+data).delay(100).animate({top:($(window).height()-550)/2},700, 'easeOutBack', function(){

					if(formAction)

						$('#lite-page').find('form').attr('action',formAction);

					stopLoading('#pageLoading');

					prevUrl = url;

				});

				$('.date').datepicker({

        			dateFormat: 'yy-mm-dd',

        			changeMonth: true,

        			changeYear: true,

        			yearRange: '-100'

    			});

			});

			

		}else{

		

			$('#lite-page').delay(100).animate({top:($(window).height()-550)/2},700, 'easeOutBack', function(){

				$('#lite-page').find('form').attr('action',url);

				stopLoading('#pageLoading');

			});

		}



		if (typeof _gaq != 'undefined') 

        	_gaq.push(['_trackPageview', url]);

        

		return false;

	});

	

	$('#lite-page').find('a.close').live('click', function() {

	

		$('#lite-page').animate({top:'-3000'},500,'easeInBack', function() {

			$('#lite-page').delay(200).fadeOut(200);

			$('#lightsOff').fadeOut();

			stopLoading('#pageLoading');

		});

		return false;

	});





	// Style file input

	$("input[type=file]").filestyle({ 

	    image: theme_base+"images/upload.jpg",

	    imageheight : 30,

	    imagewidth : 80,

	    width : 200

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

	

    $(window).keydown(function (e) {

        if (!$('#search').is(":focus") && !$('#entity').is(":focus")) {

            if (e.keyCode == 37) { // left

                playPrev();

                return false;

            } else if (e.keyCode == 39) { // right

                playNext();

                return false;

            } else if (e.keyCode == 38) { // top

                selectPrev();

                return false;

            } else if (e.keyCode == 40) { // bottom

                selectNext();

                return false;

            } else if (e.keyCode == 32 || e.keyCode == 13) { // enter or space

                if($('input[name=playlist_name]').length == 0) {

                	playSelection();

                	return false;

                }

            }

        }

    });



	

    $("#search").autocomplete({

        source: function (request, response) {

            entity = $('#entity').val();

            $.ajax({

                url: website_url + "?ajax=on&l=search",

                dataType: "json",

                cache: true,

                data: {

                    featureClass: "P",

                    style: "full",

                    maxRows: 20,

                    term: request.term,

                    entity: entity,

                },

                success: function (data) {

                    response($.map(data.results, function (item) {

                        if (entity == 'musicArtist') {

                            return {

                                label: item.artistName,

                                value: item.artistName

                            }

                        } else if (entity == 'album') {

                            return {

                                label: item.collectionName,

                                value: item.collectionName

                            }

                        } else if (entity == 'song' || entity == 'musicVideo') {

                            return {

                                label: item.trackName,

                                value: item.trackName

                            }

                        }

                    }));

                }

            });

        },

        minLength: 2,

        select: function (event, ui) {

            $('#search').val(ui.item.label);

        },

        open: function () {

            $(this).removeClass("ui-corner-all").addClass("ui-corner-top");

        },

        close: function () {

            $(this).removeClass("ui-corner-top").addClass("ui-corner-all");

        }

    });



    

    $('.ajax_qtip').each(function() {

		var url = $(this).data('url');

		$(this).qtip({

			content: {

				text: 'Loading...', // The text to use whilst the AJAX request is loading

				ajax: {

					url: url,

					type: 'GET',

					success: function(data, status) {

            			// Set the content manually (required!)

            			this.set('content.text', data);

            			if($(this.elements.content).find(".track-list").exists()) {

            				$(this.elements.content).find(".track-list").slimScroll({

								height: '270px',

								size: '2px',

								distance: '25px'

							});

						}

						if($(this.elements.content).find(".teaser").exists()) {

							$(this.elements.content).find(".teaser").slimScroll({

								height: '110px',

								size: '5px',

								distance: '5px'

							});

						}

    					stopLoading('#pageLoading');

    					if(typeof _gaq != 'undefined') { 

    						_gaq.push(['_trackPageview', url]);

    					}

    					this.reposition();

    	

         			},

					error: function() { return false; }

				}

	   		},

			position: {

				my: 'center',

				at: 'center',

				target: $(window)

			},

      		show: {

      			delay: 1100,

      			modal: {

					on: true

				}

   			},



	   		style: {

	   			classes: 'ui-tooltip-dark ui-tooltip-shadow ui-tooltip-rounded'

   			},

   			events: {

      			show: function(event, api) {

         			resetData();

         			qtipIsOpen = true;

         			stopLoading('#pageLoading');

      			},

      			hide: function(event, api) {

         			resetData();

         			qtipIsOpen = false;

         			stopLoading('#pageLoading');

      			}

   			}

		});

	});	

	

	$(".page_home .artist-list ul").slimScroll({

		height: '500px',

		size: '5px'

	});



    

    $('#searchForm').live('submit', function () {

        if ($('#search').val() == '') return false;

        var params = $(this).serialize();

        redirect('search', params);

        return false;

    });

    

	$('#innersearch').live('submit', function () {

		var loc = $(this).attr('action');

        var params = $(this).serialize();

        redirect(loc, params);

        return false;

    });

    

    $('.waitforjs').fadeIn();       

    

    $('.animateShadowHigh').hoverIntent(function() {

    	$(this).animate({boxShadow: '0 6px 10px #888'}, 800);

    }, function() {

    	$(this).animate({boxShadow: 'none'}, 500);

    });	

    



    $('#main #content div.lockup.album').hover(function() {

    

    	$(this).find('.overlay').fadeIn();

    },function() {

    	$(this).find('.overlay').hide();	

    });

       

    stLight.options({

        publisher: '3df75fda-4474-4847-8171-26c29028c7db',

        onhover: false

    });

    

    $("table.manage tbody").tableDnD({

        onDragClass: "dragging",

        onDrop: function (table, row) {

            $(table).parent().addClass('loading');

            startLoading('#pageLoading');

            var pid = $(row).data('pid');

            var params = 'ajax=on&action=reorder&pid=' + pid + '&' + $.tableDnD.serialize();

            var updateUrl = link('account.playlists', 'ajax=on');

            $.post(updateUrl, params, function (data) {

                $(table).parent().removeClass('loading');

                stopLoading('#pageLoading');

            });

        },

        onDragStart: function (table, row) {

            if ($(table).parent().hasClass('loading')) {

                return false;

            }

        }

    });

    $("select[name=entity]").linkselect();

    $("select[name=playlists]").linkselect({

        change: function (li, value, text) {

            location.href = value;

        }

    });

    $('.date').datepicker({

        dateFormat: 'yy-mm-dd',

        changeMonth: true,

        changeYear: true,

        yearRange: '-100'

    });

    $('#delete-playlist').live('click', function () {

        pid = $(this).data('pid');

        removePlaylist(pid);

        return false;

    });

    $('#rename-playlist').live('click', function () {

        $('#select-playlist').hide();

        $('#update-playlist').fadeIn().find('input[name=playlist_name]').focus();

        return false;

    });

    $('#new-playlist').live('click', function () {

        $('#select-playlist').hide();

        $('#create-playlist').fadeIn().find('input[name=playlist_name]').focus();

        return false;

    });

    $('#update-playlist input[name=cancel]').live('click', function () {

        $('#update-playlist').hide();

        $('#select-playlist').fadeIn();

        return false;

    });

    $('#update-playlist input[name=submit]').live('click', function () {

        pid = $(this).data('pid');

        pname = $('#update-playlist input[name=playlist_name]').val();

        updatePlaylist(pid, pname);

        return false;

    });

    $('#create-playlist input[name=cancel]').live('click', function () {

        $('#create-playlist').hide();

        $('#select-playlist').fadeIn();

        return false;

    });

    $('#create-playlist input[name=submit]').live('click', function () {

        pname = $('#create-playlist input[name=playlist_name]').val();

        createPlaylist(pname);

        return false;

    });

    $("#playlistsBox .closeBox").live('click', function () {

        $(this).parent().fadeOut();

        turnLights('on');

        playlistBoxIsOpen = false;

        return false;

    });
    $("#lightsOff").live('click', function () {
        turnLights('on');
		$("#playlistsBox").fadeOut();
		playlistBoxIsOpen = false;
        return false;

    });
	
    $("#createNewPlaylist #createLabel").live('click', function () {

        $(this).hide();

        $("#createNewPlaylist input[type=text]").val(default_playlist_text).addClass('empty').fadeIn().focus();

    });

    $("#createNewPlaylist input[type=text]").live('click', function () {

        if ($(this).val() == "") {

            $(this).val(default_playlist_text).removeClass('empty').addClass('empty');

        } else if ($(this).val() == default_playlist_text) {

            $(this).val('').removeClass('empty');

        }

    });

    $("#createNewPlaylist input[type=text]").live('keypress', function () {

        if ($(this).val() == default_playlist_text) {

            $(this).val('').removeClass('empty');

        }

    });

    $("#createNewPlaylist input[type=text]").live('blur', function () {

        if ($(this).val() == "" || $(this).hasClass('empty')) {

            $(this).hide();

            $("#createNewPlaylist #createLabel").show();

        }

    });

    $("#playlistsForm").live('submit', function (event) {

        if ($(this).find('input[type=text]').val() == "" || $(this).find('input[type=text]').hasClass('empty')) return false;

        playlist_name = $(this).find('input[type=text]').val();

        url = website_url + '?ajax=on&l=playlists.box&createNew=1&playlist_name=' + playlist_name;

        params = $("#playlistsForm").serialize();

        $("#playlistsBox").find('.boxContent').empty().addClass('loading');

        $.post(url, params, function (data) {

            $("#playlistsBox").find('.boxContent').removeClass('loading').html(data);

        });

        return false;

    });

    $("#playlistsForm .addToPlaylist").live('click', function () {

        if ($(this).attr('id') != "") {

            playlist_id = $(this).attr('id');

            playlist_name = $(this).attr('name');

            url = website_url + '?ajax=on&l=playlists.box&addTo=1&playlist_id=' + playlist_id + '&playlist_name=' + playlist_name;

            params = $("#playlistsForm").serialize();

            $("#playlistsBox").find('.boxContent').empty().addClass('loading');

            $.post(url, params, function (data) {

                $("#playlistsBox").find('.boxContent').removeClass('loading').html(data);

            });

        } else {

            alert('Error: Something went wrong!');

        }

        return false;

    });

    

    var shuffleSwitch;

    if($.cookie('shuffleSwitch')) {

    	shuffleSwitch = $.cookie('shuffleSwitch');

    }else{

    	shuffleSwitch = "off";

    }

    if(shuffleSwitch == "on") {

    	enableShuffle();

    }else{

    	disableShuffle();

    }

    

    $('.iswitch.shuffle').iSwitch(shuffleSwitch, 

 		function() {

 			enableShuffle();

      	},

      	function() {

      	 	disableShuffle();

      	},

      	{

        	switch_on_container_path: js_base+'iswitch/iphone_switch_container_off.png'

    	}

    );



/*        

    $.waypoints.settings.scrollThrottle = 30;

    var $toolbar = $('.playlist-wrap:not(.no-toolbar)').find('.playlist-toolbar');

	var toolbarWidth = $toolbar.width();



	$toolbar.waypoint(function(event, direction) {

		$(this).toggleClass('overlay');

	        if($(this).hasClass('overlay')) {

	            $(this).css('width', toolbarWidth+'px');

	        }else{

	            $(this).css('width', '100%');

	        }

		event.stopPropagation();

	});

*/

    

    $('a.toolbarAction').live('click', function () {

    	if($(this).hasClass('inactive')) return false;

        if (plAjaxHandler) plAjaxHandler.abort();

        if(qtipIsOpen) resetData();

        

        if ($(this).hasClass('addToPlaylist')) {

            popPlaylistBox(this);

            return false;

        }else if ($(this).hasClass('pauseall')) {

        	

            pauseCurrent();

            $(this).removeClass('pauseall').addClass('playall').find('img').attr('src', theme_base+'images/player-control/24/player-play.png');

            $(this).closest('.playlist-toolbar').find('.playnext,.playprev').addClass('inactive');

             

        }else if ($(this).hasClass('playall')) {

        

        	if (ytPlayer) {

            	playCurrent();

        	}else{

        		playFirst();

        	}

        	$(this).removeClass('playall').addClass('pauseall').find('img').attr('src', theme_base+'images/player-control/24/player-pause.png');

        	$(this).closest('.playlist-toolbar').find('.playnext,.playprev').removeClass('inactive');

         

        }else if ($(this).hasClass('playnext')) {

        	

        	playNext();

        	

        }else if ($(this).hasClass('playprev')) {

        

        	playPrev();

        }   

        

        return false;      

    }); 

     

        

    $('a.trackAction').live('click', function (e) {

    

    	if ($(this).hasClass('download') && $(this).hasClass('downloadAvailable')) {

    		return true;

    	}

    	e.preventDefault();

    	

        if (plAjaxHandler) plAjaxHandler.abort();

		hideSocial();

		

        if (ytPlayer != false) {

            pauseCurrent();

        }

        

        resetData();

        	

        $pTrackContainer = $trackContainer;

        $pTrackPlayer = $trackPlayer;

        $currentTrack = $currentSelection = $(this);

        $trackContainer = $(this).parent().parent();



        trackdata['isVideo'] = 'true';

        if($trackContainer.closest('.inQtip').length > 0)

        	trackdata['pHeight'] = qtipVideoHeight;

        else

        	trackdata['pHeight'] = videoHeight;

        	

        trackdata['hq'] = '4';

        

        classN = '';

            

        if ($(this).hasClass('download')) {

            action = "download";

            classN = 'downloading';

        } else if ($(this).hasClass('addToPlaylist')) {

            popPlaylistBox(this);

            return false;

        } else if ($(this).hasClass('remove')) {

            trackdata['pid'] = $trackContainer.data('pid');

            trackdata['trackId'] = $trackContainer.data('trackId');

            removeTrack(trackdata['pid'], trackdata['trackId']);

            return false;

        } else {

            action = "play";

            classN = 'playing';

            if($trackContainer.closest('.inQtip').length == 0)

            	$.scrollTo( $trackContainer, 800 , {offset:-100-trackdata['pHeight']});

        }



        trackdata['trackId'] = $trackContainer.data('trackId');

        trackdata['trackName'] = $trackContainer.data('trackName');

        trackdata['trackTimeMillis'] = $trackContainer.data('trackTimeMillis');

        trackdata['artistId'] = $trackContainer.data('artistId');

        trackdata['artistName'] = $trackContainer.data('artistName');

        trackdata['collectionId'] = $trackContainer.data('collectionId');

        trackdata['collectionName'] = $trackContainer.data('collectionName');

        trackdata['ytLink'] = $trackContainer.data('ytLink');

        trackdata['isVideo'] = '';

        

        trackUrl = location.href+location.hash;

        location.hash = action+'/tid/'+$trackContainer.data('trackId');    

		domain = (trackUrl.match(/:\/\/(.[^/]+)/)[1]).replace('www.','');

		urlData = trackUrl.split(domain);

		trackUrl = urlData[1];

		if (typeof _gaq != 'undefined') 

    		_gaq.push(['_trackPageview', trackUrl]);

        

        $trackPlayerWrap = $trackContainer.next().find(playerWrapSelector);

        $trackPlayer = $trackPlayerWrap.find(playerSelector);

        

        if (ytPlayer && $pTrackPlayer && $trackPlayer.attr('id') == $pTrackPlayer.attr('id') && action == 'play') {

        	setTimeout(function() {

            	ytPlayer.playVideo();

            },100);

            return false;

        }

        if(action != 'download') {

	        $('.track_row,.player_row').removeClass('selected').removeClass('playing').removeClass('downloading');

	        $('.player_row').css('display', 'none');

	        $('.player_wrap').slideUp();

	        $('.track_row .action .trackAction.play').css('visibility', 'visible');

	        $('.player').empty();

	        $trackPlayer.html('<div class="track_status"><span class="loading_track"></span>Loading track... Please wait...</div>');

	        $trackPlayerWrap.fadeIn();

	        $trackContainer.addClass(classN);

	        $trackContainer.next().addClass(classN).css('display', 'table-row');

	        $trackContainer.find('.action .trackAction.play').css('visibility', 'hidden');

		}

        plAjaxHandler = $.ajax({

            type: "POST",

            //cache: true,

            url: website_url + '?ajax=on&l=getyoutube&t=' + Math.random(),

            data: "artist=" + trackdata['artistName'] + "&song=" + trackdata['trackName'] + "&isVideo=" + trackdata['isVideo'],

            success: function (data) {

            

            	var data = $.parseJSON( data );

            	

                if (data) {

                    performAction(action, data);

                    stopLoading('#pageLoading');

                } else {

                    msg = '';

                    if (nextExists()) {

                        //msg = ' Loading next track...';

                    }

                    $trackPlayer.html('<center>Sorry, we connot find this track!' + msg + '</center>').slideDown();

                    setTimeout(function () {

                        //playNext();

                    }, 2000);

                }

            },

            error: function () {

                msg = '';

                $trackPlayer.html('<center>Cannot load track - Connection Error!</center>').slideDown();   

            }

        });

    });

    

    $(window).resize(function() {

    	if($('#lightsOff').css('display') != 'none') {

   	 		var height = $('body').height();

    		$('#lightsOff').css('height', height+'px');

    	}

	});



});





function performAction(action, data) {



    var trackId = trackdata['trackId'];

    var trackName = trackdata['trackName'];

    var trackTimeMillis = trackdata['trackTimeMillis'];

    var artistId = trackdata['artistId'];

    var artistName = trackdata['artistName'];

    var collectionId = trackdata['collectionId'];

    var collectionName = trackdata['collectionName'];

    var isVideo = trackdata['isVideo'];

    var pHeight = trackdata['pHeight'];

    var hq = trackdata['hq'];

    var fhq = '';

    var fname = '';

    var fsize = '';

    var ytlink = data.ytlink;

    var ytid = data.ytid;

    

    if (action == 'play') {

    	if(!qtipIsOpen)

    		$('.playlist-wrap:not(.no-toolbar)').find('.playlist-toolbar').find('.playall').removeClass('playall').addClass('pauseall').find('img').attr('src', theme_base+'images/player-control/24/player-pause.png');

    	else

    		$('.playlist-wrap:not(.no-toolbar)').find('.playlist-toolbar').find('.pauseall').removeClass('pauseall').addClass('playall').find('img').attr('src', theme_base+'images/player-control/24/player-play.png');



        if (!isIos()) {      

            $trackPlayer.attr('href', ytlink).customYtPlayer({

            	'ytid': ytid,

                'width': '650',

                'height': pHeight,

                'onEnd': function () {

                    playNext();

                },

                'onUnstarted': function () {



            		if($trackContainer.closest('.inQtip').length == 0)

            			$.scrollTo( $trackContainer, 800, {offset:-100});



                	ytPlayer = this.ytPlayer;

                	ytPlayer.playVideo();

                	initSocial();

                }

            });

            



        } else {

            ytlink = ytlink.replace('watch?v=', 'embed/') + '?autoplay=1';

            $trackPlayer.html('<center><a target="_blank" id="mplay" href="' + ytlink + '">Click to play...</a></center>');

        }

    } else if (action == 'download') {

		

		var downloadButton = $trackContainer.find('.action .trackAction.download');

		var downloadUrl = link('vidfetch.frame', 'ajax=on');



		$(downloadButton).qtip(

		{

			content: {

				// Set the text to an image HTML string with the correct src URL to the loading image you want to use

				text: '<center><img class="throbber" src="'+theme_base+'images/loading.gif" alt="Loading…" /></center>',

				ajax: {

					url: downloadUrl, // Use the rel attribute of each element for the url to load

					data: {ytlink:ytlink},

					type: 'POST'

				},

				title: {

					text: artistName + ' - ' + trackName + ' - Downloads', // Give the tooltip a title using each elements text

					button: true

				}

			},

			position: {

				at: 'top center', // Position the tooltip above the link

				my: 'bottom left',

				viewport: $(window), // Keep the tooltip on-screen at all times

				adjust: {

					resize: true

				}

			},

			show: {

				event: 'click',

				solo: true // Only show one tooltip at a time

			},

			hide: false,

			style: {

				classes: 'ui-tooltip-wiki ui-tooltip-light ui-tooltip-shadow downloads'

			}

		});



		$(downloadButton).qtip('show');





    } else {

        alert("Invalid Action!");

    }



}





function initSocial() {

	if(FB) {

		var link = $trackContainer.find('td.name a').data('href');

		var comments_width = 714;

		if(CPAGE == 'home') 

			comments_width = 946;

		else if(CPAGE == 'playlist') 

			comments_width = 670;	

			

		var fb_comments = '<center><div class="fb-comments margin5" data-href="'+link+'" data-num-posts="5" data-width="'+comments_width+'"></div></center>';

		var fb_like = '<div class="fb-like trackAction marginL5" data-href="'+link+'" data-send="false" data-layout="button_count" data-width="50" data-show-faces="false" data-font="arial"></div>';

		

		$trackContainer.find('td.action .fb-like-wrap').replaceWith(fb_like);

		$trackContainer.next().find('td .fb-comments-wrap').replaceWith(fb_comments);

		setTimeout(function() {

			FB.XFBML.parse();

			$trackContainer.find('.playlist-wrap .fb-comments, .playlist-wrap .fb-like').show();

		},500);

	}	

}



function hideSocial() {



	$('.playlist-wrap .fb-comments, .playlist-wrap .fb-like').hide();



}





function enableShuffle() {



	shuffleEnabled = true;

 	if($currentTrack) {

 		var track_row = $currentTrack.closest('.playlist-wrap:not(.no-toolbar)').find('.track_row');

       			

	}else{

		var track_row = $('.playlist-wrap:not(.no-toolbar)').find('.track_row');

	}

			

	var elements = track_row.get();

	shuffleData = elements.sort(function(){ 

  		return Math.round(Math.random())-0.5

	});

	$.cookie('shuffleSwitch', "on");

}



function disableShuffle() {



	shuffleEnabled = false;

	shuffleIndex = 0;

	shuffleData = {};

	$.cookie('shuffleSwitch', "off");

}









function nextExists() {

    if ($currentTrack.parent().parent().next().next().find('.trackAction.play').exists()) return true;

    else return false;

}



function prevExists() {

    if ($currentTrack.parent().parent().prev().prev().find('.trackAction.play').exists()) return true;

    else return false;

}



function selectNextExists() {

    if ($currentSelection.parent().parent().next().next().find('.trackAction.play').exists()) return true;

    else return false;

}



function selectPrevExists() {

    if ($currentSelection.parent().parent().prev().prev().find('.trackAction.play').exists()) return true;

    else return false;

}



function playFirst() {

	$('.playlist-wrap:not(.no-toolbar)').find('.track_row.first-track').find('.action .play').trigger('click');

}



function playNext() {

	if(shuffleEnabled) {

		if(shuffleIndex >= (shuffleData.length-1))

			shuffleIndex = 0;

		else	

			shuffleIndex++;	

			

		$(shuffleData[shuffleIndex]).find('.trackAction.play').trigger('click').focus();

		

	}else{

		if($currentTrack) 

    		$currentTrack.parent().parent().next().next().find('.trackAction.play').trigger('click').focus();

    	else

    		playFirst();	

	}

}



function playPrev() {



	if(shuffleEnabled) {

		if(shuffleIndex <= 0)

			shuffleIndex = shuffleData.length - 1;

		else	

			shuffleIndex--;	

			

		$(shuffleData[shuffleIndex]).find('.trackAction.play').trigger('click').focus();

		

		

	}else{

		if($currentTrack)

    		$currentTrack.parent().parent().prev().prev().find('.trackAction.play').trigger('click').focus();

    	else

    		playFirst();	

    }

}



function playCurrent() {

	if(ytPlayer)

    	ytPlayer.playVideo();

}

function pauseCurrent() {

	setTimeout(function() {

		if (ytPlayer != false) {

    	    ytPlayer.pauseVideo();

    	}

	},100);

	

}

function selectNext() {

    if (selectNextExists()) {

        $currentSelection.parent().parent().removeClass('selected');

        $currentSelection = $currentSelection.parent().parent().next().next();

        $currentSelection.addClass('selected');

        $currentSelection = $currentSelection.find('.trackAction.play');

        $currentSelection.focus();

    }

}



function selectPrev() {

    if (selectPrevExists()) {

        $currentSelection.parent().parent().removeClass('selected');

        $currentSelection = $currentSelection.parent().parent().prev().prev();

        $currentSelection.addClass('selected');

        $currentSelection = $currentSelection.find('.trackAction.play');

        $currentSelection.focus();

    }

}



function playSelection() {

    $currentSelection.trigger('click').focus();

}



function tooManyRequests($trackPlayer) {

    $trackPlayer.html('<center>Too many download requests at this time! <br>Please try in a couple of minutes...</center>');

    return -1;

}



function checkProgress($trackPlayer, fname, fsize, fhq) {

    $grabStatus = $trackPlayer.find('.grabstatus');

    $.ajax({

        type: "POST",

        //dataType: 'html',

        //crossDomain: true,

        url: website_url + '?ajax=on&l=ytconv',

        //url: 'http://ytconv.com/check-size.php',

        data: "type=checksize&fname=" + fname + "&fsize=" + fsize + "&hq=" + fhq,

        success: function (progress) {

            $grabStatus.html(progress);

        }

    });

}



function popPlaylistBox(obj) {

    if (playlistBoxIsOpen) {

        $("#createNewPlaylist input[type=submit]").trigger('click');

        return false;

    }

    turnLights('off');

    $("#playlistsBox").hide().fadeIn().position({

        my: "right top",

        at: "right bottom",

        of: $(obj),

        collision: "fit"

    }).maxZIndex({ inc: 5 });

    

    turnLights('off');

    playlistBoxIsOpen = true;

    $("#playlistsBox").find('.boxContent').empty().addClass('loading');

    

    var multiple = false;

    if($(obj).hasClass('allSongs'))

    	multiple = true;

    	

    var postData = {}	

    

    if(multiple) {

    

    	$(obj).closest('.playlist-wrap').find('.track_row').each(function(i) {

    		postData[i] = {}

       		$track = $(this);

    		postData[i]["trackId"] = $track.data('trackId');

    		postData[i]["trackName"] = $track.data('trackName');

    		postData[i]["trackTimeMillis"] = $track.data('trackTimeMillis');

    		postData[i]["collectionId"] = $track.data('collectionId');

    		postData[i]["collectionName"] = $track.data('collectionName');

    		postData[i]["artistId"] = $track.data('artistId');

    		postData[i]["artistName"] = $track.data('artistName');

    		postData[i]["isVideo"] = $track.data('isVideo'); 	

    	});

		

    	

    }else{

    	var i = 0;

    	postData[i] = {}

    	$track = $(obj).parent().parent();

    	postData[i]["trackId"] = $track.data('trackId');

    	postData[i]["trackName"] = $track.data('trackName');

    	postData[i]["trackTimeMillis"] = $track.data('trackTimeMillis');

    	postData[i]["collectionId"] = $track.data('collectionId');

    	postData[i]["collectionName"] = $track.data('collectionName');

    	postData[i]["artistId"] = $track.data('artistId');

    	postData[i]["artistName"] = $track.data('artistName');

    	postData[i]["isVideo"] = $track.data('isVideo');    	

    }



    var url = website_url + '?ajax=on&l=playlists.box&t=' + Math.random();



	$.post(url, postData, function (data) {

		$("#playlistsBox").find('.boxContent').removeClass('loading').html(data);

		$("#playlistsBox").find('.boxContent ul').slimScroll({

			height: '100px',

			size: '10px'

		});

		turnLights('off');

	});



}







function resetData() {

	trackdata = [];

	$currentTrack = null;

	$currentSelection = null;

	$trackContainer = null;

	$trackPlayer = null;

	$trackPlayerWrap = null;

	ytPlayer = false;

}

function removeTrack(pid, trackId) {

    if (confirm("Do you really want to remove this track?")) {

        $('table #playlist').parent().addClass('loading');

        $.post(website_url + '?ajax=on&l=account.playlists', 'action=rmtrack&pid=' + pid + '&trackId=' + trackId, function (data) {

            $('table #playlist').parent().removeClass('loading');

            $('#' + trackId).fadeOut(500).remove();

        });

    }

}



function removePlaylist(pid) {

    if (confirm("Do you really want to delete this playlist?")) {

        $('table #playlist').parent().addClass('loading');

        $.post(website_url + '?ajax=on&l=account.playlists', 'action=rmplaylist&pid=' + pid, function (data) {

            redirect('account.playlists', 'removeSuccess=true');

        });

    }

}



function updatePlaylist(pid, pname) {

    $('table #playlist').parent().addClass('loading');

    $.post(website_url + '?ajax=on&l=account.playlists', 'action=update&pid=' + pid + '&pname=' + pname, function (data) {

        tmp = data.split('updated_');

        pid = tmp[1];

        redirect('account.playlists', 'id=' + pid + '&updateSuccess=true');

    });

}



function createPlaylist(pname) {

    $('table #playlist').parent().addClass('loading');

    $.post(website_url + '?ajax=on&l=account.playlists', 'action=new&pname=' + pname, function (data) {

        tmp = data.split('created_');

        pid = tmp[1];

        redirect('account.playlists', 'id=' + pid + '&createSuccess=true');

    });

}



function checkOtherPicture(obj) {

    var total = $('.artist-thumb').size();

    var index = Math.floor(Math.random() * total);

    $('.artist-thumb').eq(index).find('img').each(function () {

        var link = $(this).attr('src');

        $(obj).parent().attr('href', link);

        $(obj).attr('src', link);

    });

}



function maxchars(field) {

    if (field.value.length > 500) field.value = field.value.substring(0, 500);

    else document.getElementById('remaining').innerHTML = 500 - field.value.length;

}



function popup(mypage, myname, w, h) {

    LeftPosition = (screen.width) ? (screen.width - w) / 2 : 0;

    TopPosition = (screen.height) ? (screen.height - h) / 2 : 0;

    settings = 'location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=no,height=' + h + ',width=' + w + ',top=' + TopPosition + ',left=' + LeftPosition + ',resizable'

    window.open(mypage, myname, settings)

}



function reloadCaptcha() {

    Recaptcha.reload();

}



function failedCaptcha() {

    alert("The reCAPTCHA wasn't entered correctly. \nPlease try again!");

}



function hideCaptcha() {

    $('#captcha').fadeOut();

}



function isNumeric(sText) {

    var ValidChars = "0123456789.";

    var IsNumber = true;

    var Char;

    for (i = 0; i < sText.length && IsNumber == true; i++) {

        Char = sText.charAt(i);

        if (ValidChars.indexOf(Char) == -1) {

            IsNumber = false;

        }

    }

    return IsNumber;

}



function urlencode(str) {

    return escape(str.replace(/%/g, '%25').replace(/\+/g, '%2B')).replace(/%25/g, '%');

}



function isIos() {

    var deviceAgent = navigator.userAgent.toLowerCase();

    var ios = deviceAgent.match(/(iphone|ipod|ipad)/);

    return ios;

}



function turnLights(action) {

    if (action != "off") {

        $('#lightsOff').hide();

    } else {

        bodyHeight = ($('body').height()) + 'px';

        $('#lightsOff').css('height', bodyHeight).fadeIn();

    }

}



function startLoading(selector) {

    $(selector).show();

}



function stopLoading(selector) {

    $(selector).hide();

}



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
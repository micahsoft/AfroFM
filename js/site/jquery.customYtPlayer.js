/*! Copyright © 2011 Domenico Gigante (http://scripts.reloadlab.net)
 * 
 * Dual licensed under:
 * 
 * 1) MIT (http://www.opensource.org/licenses/mit-license.php)
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * 2) GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * CustomScroller is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * CustomYtPlayer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with CustomYtPlayer.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Thanks to: Martin Angelov (http://tutorialzine.com/2010/07/youtube-api-custom-player-jquery-css/) for his tutorial.
 * Thanks for the community that is helping the improvement
 * of this little piece of code.
 *
 * Version: 1.0.0
 * Date: 28th Mar, 2011
 * 
 * Requires: jQuery 1.3.2+
 */

(function ($) {
	$.fn.customYtPlayer = function (options) {
		
		var youtubeAPI = "http://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc";
		var settings = {};
		var interval = false;
		
		
		// Default values
		options = $.extend({
			ytid: null,
			width: 640,
			height: 24,
			onPlay: null,
			onPause: null,
			onEnd: null,
			onUnstarted: null,
			ytPlayer: false
		},
		options);
		
		// Format Timer (hh:mm:ss)
		function formatTime (second, hour, minute) {
			if (second > 3600) {
				var ore = Math.floor(second / 3600);
				if(ore < 10) ore = "0" + ore;
				var rest = Math.ceil(second % 3600);
				var format = formatTime (rest, ore);
			} else if (second > 60) {
				var minuti = Math.floor(second / 60);
				if(minuti < 10) minuti = "0" + minuti;
				var rest = Math.ceil(second % 60);
				var format = formatTime (rest, ore, minuti);
			} else if (second < 60) {
				if (!hour) hour = "00";
				if (!minute) minute = "00";
				if (!second) {
					second = "00";
				} else {
					second = Math.round(second);
					if (second < 10) {
						second = "0" + second;
					}
				}
				var format = hour + ":" + minute + ":" + second;
			}
			return format;
		};
		
		// Video ID Extractor
		function youtubeIDextract (url) { 
			var re = /(\?v=|\/\d\/|\/embed\/|\/v\/|\.be\/)([a-zA-Z0-9\-\_]+)/;
			if (url.match(re)[2]) {
			
				return url.match(re)[2];
				
			} else {
				return false;
			}
		}

		if (this.length > 0) {
			
			var lastDate;
			
			this.each(function (index, domElement) {
				
				// UNIQUE ID
				// is an unique identifier of video, ready for use as a function name
				var curDate = new Date().getTime();
				var uniqId = (lastDate === curDate) ? (curDate + 1000) : curDate;
				
				// SETTINGS
				settings[uniqId] = {};
				settings[uniqId].that = $(this);
				settings[uniqId].safeID = uniqId;
				
				if(options.ytid) {
				
					videoID = options.ytid;
					var href = $(this).attr("href");
		
				}else{
				
					// CHECK VIDEO URL AND EXTRACT VIDEO ID
					var href = $(this).attr("href");
				
					if (!href || !href.length) {
						return false;
					} else {
			
						videoID = youtubeIDextract(href);
						if(!videoID)
							return false;
					}
				}
				
				// WIDTH
				var width = $(this).width();
				settings[uniqId].width = (!width || width <= 0) ? Math.round(options.width) : Math.round(width);
				settings[uniqId].height = options.height;
				
				// Fetch data about the video from YouTube's API
				$.get(youtubeAPI, {'q': videoID}, function (response) {
					
					// If the video was not found, or embedding is not allowed;
					if (!response.data.totalItems || response.data.items[0].accessControl.embed != "allowed" ) {
						return false;
					}
					
					// data holds API info about the video:
					var data = {};
					data = response.data.items[0];
					
					// VIDEO ID
					settings[uniqId].videoID = data.id;
					
					// ASPECT RATIO
					settings[uniqId].ratio = 3 / 4;
					if (data.aspectRatio == "widescreen") {
						settings[uniqId].ratio = 9 / 16;
					}
					
					// HEIGHT
					//settings[uniqId].height = Math.round(settings[uniqId].width * settings[uniqId].ratio);
					
					// REPLACE LINK TAG WITH A DIV
					settings[uniqId].that.html("<div id=\"customYtPlayer_" + uniqId + "\"></div>");
					
					// CONTAINER
					var elements = {};
					elements.container = $("#customYtPlayer_" + uniqId).addClass("flashContainer").css({
						'width': settings[uniqId].width + "px",
						'height': settings[uniqId].height + "px"
					});
					
					// INSERT FLASH PLAYER
					elements.container.flash({
						swf: "http://www.youtube.com/apiplayer?enablejsapi=1&version=3",
						id: "video_" + settings[uniqId].safeID,
						height: settings[uniqId].height,
						width: settings[uniqId].width,
						allowScriptAccess: "always",
						wmode: "transparent",
						flashvars: {
							"video_id" : settings[uniqId].videoID,
							"playerapiid" : settings[uniqId].safeID,
							"autoplay" : 1
						}
					});
					
					// THE FLASH PLAYER
					// We use get, because we need the DOM element
					// itself, and not a jquery object:
					elements.player = elements.container.flash().get(0);
					options.ytPlayer = elements.player;
					
					// PLAYER CONTROLLER
					elements.control = $("<div class=\"flashControl\"></div>").appendTo(elements.container); // A Controller container div, inserted by the plugin
					// PLAY/PAUSE ON CLICK EVENT
					elements.play = $("<a href=\"#\" class=\"flashPlay\"></a>").appendTo(elements.control) // The control play/pause button
					.click(function (e) {
						if(!elements.container.hasClass("playing")){
							elements.player.playVideo();
						} else {
							elements.player.pauseVideo();
						}
						return false;
					});
					elements.current = $("<div class=\"flashCurrent\"></div>").appendTo(elements.control); // Current elapsed time
					elements.volume = $("<div class=\"flashVolume\"></div>").appendTo(elements.control); // The control sound volume
					for (var i = 0; i < 10; i++) {
						// VOLUME ON CLICK EVENT
						$("<div rel=\"volume_" + i + "\" class=\"flashCursor\"></div>").appendTo(elements.volume) // The control sound volume cursor
						.click(function (e) {
							var index = $(this).attr("rel").replace("volume_", "");
							if (index >= 0 && index < 10) {
								$("div.flashCursor:lt(" + (index + 1) + ")", elements.control).addClass("selected");
								$("div.flashCursor:gt(" + index + ")", elements.control).removeClass("selected");
								volume = (Math.round(index) + 1) * 10;
								if (volume < 0) volume = 0;
								if (volume > 100) volume = 100;
								elements.player.setVolume(volume);
							}
							return false;
						});
					}
					$("<div class=\"flashClear\"></div>").appendTo(elements.volume)
					// MUTE ON CLICK EVENT
					elements.mute = $("<a href=\"#\" class=\"flashMute\"></a>").appendTo(elements.control) // The control sound mute
					.click(function (e) {
						if(elements.player.isMuted()){
							elements.container.removeClass("muted");
							elements.player.unMute();
						} else {
							elements.container.addClass("muted");
							elements.player.mute();
						}
						return false;
					});
					elements.duration = $("<div class=\"flashDuration\"></div>").appendTo(elements.control); // Duration of the video
					elements.progress = $("<div class=\"flashProgress\"></div>").appendTo(elements.control); // Progress bar
					elements.elapsed = $("<div class=\"flashElapsed\"></div>").appendTo(elements.progress); // The light blue elapsed bar
					$("<div class=\"flashClear\"></div>").appendTo(elements.control);
					
					// START TIME AND DURATION
					elements.current.html(formatTime(0));
					elements.duration.html(formatTime(data.duration));
					
					// PROGRESS BAR ON CLICK EVENT
					elements.progress.click(function (e) {
						// When a click occurs on the progress bar, seek to the
						// appropriate moment of the video.
						var ratio = (e.pageX - elements.progress.offset().left) / elements.progress.outerWidth();
						elements.elapsed.width((ratio * 100) + "%");
						elements.player.seekTo(Math.round(data.duration * ratio), true);
						return false;
					});

					
					// PLAYER EVENT LISTENER
					// Creating a global event listening function for the video
					// (required by YouTube's player API):
					window["eventListener_" + settings[uniqId].safeID] = function (status) {
						if (status == -1) {
							// unstarted 
							if(elements.player.isMuted()){
								elements.container.addClass("muted");
							} else {
								elements.container.removeClass("muted");
							}
							
							var volume = elements.player.getVolume();
							var index = Math.round(volume / 10);
							$("div.flashCursor:lt(" + (index + 1) + ")", elements.control).addClass("selected");
							
							// On Unstarted Event
							if($.isFunction(options.onUnstarted)){
								options.onUnstarted(elements.container);
							}
						}
						else if (status == 0) {
							// ended 
							window.clearInterval(interval);
							interval = false;
							elements.current.html(formatTime(0));
							elements.elapsed.width("0");
							elements.container.removeClass("playing");
							elements.progress.removeClass("buffering");
							elements.elapsed.removeClass("progressing");
							
							// On End Event
							if($.isFunction(options.onEnd)){
								options.onEnd(elements.container);
							}
						}
						else if (status == 1) {

							// playing 
							elements.container.addClass("playing");
							elements.progress.addClass("buffering");
							elements.elapsed.addClass("progressing");
							
							// Progress Bar updater
							if (!interval) {
								interval = window.setInterval(function () {
									if(elements.player.getCurrentTime) {
										elements.elapsed.width(((elements.player.getCurrentTime() / data.duration) * 100) + "%");
										elements.current.html(formatTime(elements.player.getCurrentTime()));
									}
								}, 1000);
							}
							
							// On Play Event
							if($.isFunction(options.onPlay)){
								options.onPlay(elements.container);
							}
						}
						else if (status == 2) {
							// paused 
							window.clearInterval(interval);
							interval = false;
							elements.container.removeClass("playing");
							elements.progress.removeClass("buffering");
							
							// On Pause Event
							if($.isFunction(options.onPause)){
								options.onPause(elements.container);
							}
						}
						else if (status == 3) {
							// buffering 
							elements.progress.addClass("buffering");
							elements.elapsed.addClass("progressing");
						}
						else if (status == 5) {
							// video cued 
							// Actually not used
						}
					}
					
					// This global function is called when the player is loaded.
					// It is shared by all the videos on the page:
					if (!window.onYouTubePlayerReady) {				
						window.onYouTubePlayerReady = function (playerID) {
							document.getElementById("video_" + playerID).addEventListener("onStateChange", "eventListener_" + playerID);
						}
					}

				},"jsonp");
				
				lastDate = curDate;
				
			});
		}
	};
})(jQuery);
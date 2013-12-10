/* 
 * Copyright 2013 lambda2.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

(function($) {

  function toggleFullScreen(elt) {
    
  }

	window.drafter = {};

	window.drafter.fscreen = function (element)
	{
  		var elt = document.getElementById("aera");

		if ($(elt).hasClass("fullscreen"))
		{
			console.log("exitfullscreen");
	    	$(elt).removeClass("fullscreen");
	    	$('[data-action="fullscreen"]').html('<span class="icon-expand2"></span> <span class="legend">Full screen</span>');
			if (document.mozCancelFullScreen !== undefined)
		      {
		        document.mozCancelFullScreen();
		      }
		      else
		      {
		        document.webkitCancelFullScreen();
		      }
		      return;
		}
		else if (!document.mozFullScreen && !document.webkitFullScreen)
		{
			console.log("fullscreen");
	    	$(elt).addClass("fullscreen");

	    	$('[data-action="fullscreen"]').html('<span class="icon-contract2"></span> <span class="legend">Normal screen</span>');
	    	/*$('article#aera.fullscreen [data-action="fullscreen"]').unbind( "click" ).click(function()
	    	{
	    		window.drafter.fscreen(elt);
	    	});*/

		    if (elt.mozRequestFullScreen)
		    {
		      elt.mozRequestFullScreen();
		    }
		    else
		    {
		      elt.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
		    }
			return;
	    }
	}

})(jQuery);

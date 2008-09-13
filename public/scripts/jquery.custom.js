// by Matej Udovicic
// http://www.udovicic.com

$.fn.check = function(mode) {
	var mode = mode || 'on';
	return this.each(function() {
		switch(mode) {
		case 'on':
			this.checked = true;
			break;
		case 'off':
			this.checked = false;
			break;
		case 'toggle':
			this.checked = !this.checked;
			break;
		}
	});
};

$.fn.checkCount = function() {
	var count = 0;
	this.each(function() {
		if(this.checked && this.className !="nocount") count++;
	});
	return count;
};


  	function delayFadeOut()
  	{
  		setTimeout('$("#message").fadeOut(2000)', 4000);
  	}

 	function showMessage(message)
 	{
 				$("#message").fadeOut(0);
 				$("#message").text(message);
 				$("#message").fadeIn(0);
 				delayFadeOut();
 	}
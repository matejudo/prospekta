//
//	jQuery Slug Generation Plugin by Perry Trinier (perrytrinier@gmail.com)
//  Licensed under the GPL: http://www.gnu.org/copyleft/gpl.html

jQuery.fn.slug = function(options) {

	var settings = {
		slug: 'slug', // Class used for slug destination input and span. The span is created on $(document).ready() 
		hide: true	 // Boolean - By default the slug input field is hidden, set to false to show the input field and hide the span. 
	};
		
	if(options) {
		jQuery.extend(settings, options);
	}
	
	$this = $(this);

	$(document).ready( function() {
		if (settings.hide) {
			$('input.' + settings.slug).after("<span class="+settings.slug+"></span>");
			$('input.' + settings.slug).hide();
		}
	});
	
	makeSlug = function() {
		if(!lock )
		{
			var slugcontent = $this.val();
			var slugcontent = slugcontent.replace(/[ČčĆć]/g,'c');
			var slugcontent = slugcontent.replace(/[Šš]/g,'s');
			var slugcontent = slugcontent.replace(/[Đđ]/g,'dj');
			var slugcontent = slugcontent.replace(/[Žž]/g,'z');
			var slugcontent_hyphens = slugcontent.replace(/\s/g,'-');
			var finishedslug = slugcontent_hyphens.replace(/[^a-zA-Z0-9\-]/g,'');
			$('input.' + settings.slug).val(finishedslug.toLowerCase());
			$('span.' + settings.slug).text(finishedslug.toLowerCase());
		}
	}
		
	$(this).keyup(makeSlug);
		
	return $this;
};



jQuery.fn.myslug = function(options) {
	
	$this = $(this);
	makeSlug = function() {
		if(!lock || $(this).attr("id") == "slug" )
		{
			var slugcontent = $(this).val();
			var slugcontent = slugcontent.replace(/[ČčĆć]/g,'c');
			var slugcontent = slugcontent.replace(/[Šš]/g,'s');
			var slugcontent = slugcontent.replace(/[Đđ]/g,'dj');
			var slugcontent = slugcontent.replace(/[Žž]/g,'z');
			var slugcontent_hyphens = slugcontent.replace(/\s/g,'-');
			var finishedslug = slugcontent_hyphens.replace(/[^a-zA-Z0-9\-]/g,'');
			$('#slug').val(finishedslug.toLowerCase());
		}
	}		
	$(this).keyup(makeSlug);
	return $this;
};

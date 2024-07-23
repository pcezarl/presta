(function($){

$.bsformaccelerator = function(id,options){

defaulthandle = "enter";


if(options) {
if(!options.handle) {
handle = defaulthandle;
}else {
handle = options.handle;
}
}else {
handle = defaulthandle;
}

$("#"+id+" input").keypress(function (e) {

      if(handle == 'enter') {
		  hCode = 13;
		  }else if(handle == 'space') {
		  hCode = 32;
		  }
      if(e.which == hCode) {

		  tabindex = $(this).attr('tabindex');
		  tabindex++;
		  $("input[tabindex='"+tabindex+"']").focus();
          return false;
	  
		  }
});


};

})(jQuery);
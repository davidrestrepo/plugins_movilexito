(function($){
	$(document).on('keydown', 'input[name=cun]', function(e) {
    	if (e.keyCode == 32) return false;
	});
})(jQuery);

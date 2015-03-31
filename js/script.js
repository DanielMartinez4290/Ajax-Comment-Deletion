jQuery(document).ready(function($) {

	$('.dm_acd_link').click(function(){
		var link = this;
		// get comment id and nonce
		var href = $(link).attr( 'href' );
		var id = href.replace(/^.*c=(\d+).*$/, '$1');
		var nonce = href.replace(/^.*_wpnonce=([a-z0-9]+).*$/, '$1');

		var data = {
			action: 'dm_acd_ajax_delete', 
			cid: id,
			nonce: nonce
		}

		$.post( dm_acd.ajaxurl, data, function(data){
			var status = $(data).find('response_data').text();
			var message = $(data).find('supplemental message').text(); 
			if( status == 'success' ) {
				$(link).parent().after( '<p><b>'+message+'</b></p>' ).remove(); 
			} else {
				alert( message ); 
			}
		});

		return false; 
	});
});
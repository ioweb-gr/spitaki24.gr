/* Javascript Document */
jQuery(document).ready(function($){
	jQuery('select.commercekit-select2').each(function(){
		add_commercekit_select2(jQuery(this))
	});
});
function add_commercekit_select2(obj){
	var type = obj.data('type');
	var placeholder = obj.data('placeholder');
	var nonce = jQuery('#commercekit_nonce').val();
	obj.select2({
		placeholder: placeholder,
		ajax: {
			url: ajaxurl,
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					type: type,
					commercekit_nonce: nonce,
					action: 'commercekit_sg_get_pcids'
				};
			},
			processResults: function( data ) {
				var options = [];
				if ( data ) {
					jQuery.each( data, function( index, text ) {
						options.push( { id: text[0], text: text[1] } );
					});
				}
				return {
					results: options
				};
			},
			cache: true
		},
		minimumInputLength: 3
	});
}

( function ( $ ) {
	'use strict';

	let frame;
	const $id = $( '#parish-bulletin-pdf-id' );
	const $name = $( '#parish-bulletin-pdf-name' );
	const $select = $( '#parish-bulletin-select-pdf' );
	const $remove = $( '#parish-bulletin-remove-pdf' );

	$select.on( 'click', function ( event ) {
		event.preventDefault();

		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media( {
			title: parishBulletinsAdmin.title,
			button: { text: parishBulletinsAdmin.button },
			library: { type: 'application/pdf' },
			multiple: false,
		} );

		frame.on( 'select', function () {
			const attachment = frame.state().get( 'selection' ).first().toJSON();
			$id.val( attachment.id );
			$name.text( attachment.filename || attachment.title );
			$select.text( parishBulletinsAdmin.replaceLabel );
			$remove.removeClass( 'hidden' );
		} );

		frame.open();
	} );

	$remove.on( 'click', function ( event ) {
		event.preventDefault();
		$id.val( 0 );
		$name.text( parishBulletinsAdmin.empty );
		$select.text( parishBulletinsAdmin.selectLabel );
		$remove.addClass( 'hidden' );
	} );
}( jQuery ) );

( function ( wp ) {
	'use strict';

	const el = wp.element.createElement;
	const useSelect = wp.data.useSelect;
	const useDispatch = wp.data.useDispatch;
	const PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	const Button = wp.components.Button;
	const Notice = wp.components.Notice;
	const TextControl = wp.components.TextControl;
	const MediaUpload = wp.blockEditor.MediaUpload;
	const MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	const __ = wp.i18n.__;

	function ordinalSuffix( day ) {
		const remainder = day % 100;

		if ( remainder >= 11 && remainder <= 13 ) {
			return 'th';
		}

		switch ( day % 10 ) {
			case 1:
				return 'st';
			case 2:
				return 'nd';
			case 3:
				return 'rd';
			default:
				return 'th';
		}
	}

	function titleFromDate( value ) {
		const parts = value.split( '-' ).map( Number );
		if ( parts.length !== 3 || ! parts[ 0 ] || ! parts[ 1 ] || ! parts[ 2 ] ) {
			return '';
		}

		const date = new Date( Date.UTC( parts[ 0 ], parts[ 1 ] - 1, parts[ 2 ] ) );
		if (
			date.getUTCFullYear() !== parts[ 0 ] ||
			date.getUTCMonth() !== parts[ 1 ] - 1 ||
			date.getUTCDate() !== parts[ 2 ]
		) {
			return '';
		}

		const month = new Intl.DateTimeFormat( undefined, { month: 'long', timeZone: 'UTC' } ).format( date );
		return __( 'Bulletin - ', 'parish-bulletins' ) + month + ' ' + parts[ 2 ] + ordinalSuffix( parts[ 2 ] ) + ', ' + parts[ 0 ];
	}

	function BulletinDetailsPanel() {
		const postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		const postStatus = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'status' ) || 'draft';
		}, [] );
		const meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		const pdfId = Number( meta._parish_bulletin_pdf_id || 0 );
		const pdf = useSelect( function ( select ) {
			return pdfId ? select( 'core' ).getEntityRecord( 'postType', 'attachment', pdfId ) : null;
		}, [ pdfId ] );
		const editPost = useDispatch( 'core/editor' ).editPost;

		if ( postType !== 'parish_bulletin' ) {
			return null;
		}

		function updateMeta( key, value ) {
			editPost( { meta: Object.assign( {}, meta, { [ key ]: value } ) } );
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'parish-bulletin-details',
				title: __( 'Bulletin Details', 'parish-bulletins' ),
				className: 'parish-bulletin-editor-panel',
			},
			postStatus === 'publish'
				? el( Notice, { status: 'success', isDismissible: false }, __( 'Published — this bulletin is visible to visitors.', 'parish-bulletins' ) )
				: el( Notice, { status: 'info', isDismissible: false }, __( 'Draft — this bulletin is private and will not appear on the website until you publish it.', 'parish-bulletins' ) ),
			el( TextControl, {
				label: __( 'Bulletin date', 'parish-bulletins' ),
				type: 'date',
				value: meta._parish_bulletin_date || '',
				onChange: function ( value ) {
					const changes = {
						meta: Object.assign( {}, meta, { _parish_bulletin_date: value } ),
					};
					const generatedTitle = titleFromDate( value );
					if ( generatedTitle ) {
						changes.title = generatedTitle;
					}
					editPost( changes );
				},
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
			} ),
			el(
				'div',
				{ className: 'parish-bulletin-editor-pdf' },
				el( 'p', null, el( 'strong', null, __( 'Bulletin PDF', 'parish-bulletins' ) ) ),
				pdfId
					? el( Notice, { status: 'info', isDismissible: false }, pdf ? ( pdf.filename || pdf.title.rendered ) : __( 'Loading selected PDF…', 'parish-bulletins' ) )
					: el( Notice, { status: 'warning', isDismissible: false }, __( 'No PDF selected.', 'parish-bulletins' ) ),
				el(
					MediaUploadCheck,
					null,
					el( MediaUpload, {
						onSelect: function ( media ) {
							updateMeta( '_parish_bulletin_pdf_id', Number( media.id ) );
						},
						allowedTypes: [ 'application/pdf' ],
						value: pdfId,
						render: function ( renderProps ) {
							return el( Button, { variant: 'secondary', onClick: renderProps.open }, pdfId ? __( 'Replace PDF', 'parish-bulletins' ) : __( 'Select or Upload PDF', 'parish-bulletins' ) );
						},
					} )
				),
				pdfId
					? el( Button, {
						variant: 'tertiary',
						isDestructive: true,
						onClick: function () {
							updateMeta( '_parish_bulletin_pdf_id', 0 );
						},
					}, __( 'Remove PDF', 'parish-bulletins' ) )
					: null
			),
			el( 'p', { className: 'components-base-control__help' }, __( 'Use the excerpt for a short archive description and the editor for future e-bulletin content.', 'parish-bulletins' ) )
		);
	}

	wp.plugins.registerPlugin( 'parish-bulletins', {
		render: BulletinDetailsPanel,
		icon: 'media-document',
	} );
}( window.wp ) );

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
					updateMeta( '_parish_bulletin_date', value );
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

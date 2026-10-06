/**
 * Editor integration — no build step, uses WordPress globals.
 * 1. Live previews for the dynamic blocks.
 * 2. A "Spot details" panel for structured fields (ratings, address, map pin…).
 */
( function ( wp ) {
	const { createElement: el, Fragment } = wp.element;
	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, RangeControl, ToggleControl, TextControl, SelectControl, Notice } = wp.components;
	const ServerSideRender = wp.serverSideRender;

	const preview = ( name ) => ( props ) =>
		el( 'div', useBlockProps(), el( ServerSideRender, { block: name, attributes: props.attributes, urlQueryArgs: { post_id: wp.data.select( 'core/editor' )?.getCurrentPostId?.() } } ) );

	registerBlockType( 'study-spots/spot-grid', {
		edit( props ) {
			const { attributes: a, setAttributes: set } = props;
			return el( Fragment, null,
				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Grid settings', 'study-spots' ) },
						el( RangeControl, { label: __( 'Number of spots (0 = all)', 'study-spots' ), min: 0, max: 24, value: a.limit < 0 ? 0 : a.limit, onChange: ( v ) => set( { limit: v ? v : -1 } ) } ),
						el( ToggleControl, { label: __( 'Show search & filters', 'study-spots' ), checked: a.showFilters, onChange: ( v ) => set( { showFilters: v } ) } ),
						el( ToggleControl, { label: __( 'Hide the spot being viewed', 'study-spots' ), help: __( 'Use on single spot templates for “more spots”.', 'study-spots' ), checked: a.excludeCurrent, onChange: ( v ) => set( { excludeCurrent: v } ) } ),
						el( SelectControl, {
							label: __( 'Card heading level', 'study-spots' ),
							help: __( 'Pick the level that follows the heading above this block, so the outline stays logical.', 'study-spots' ),
							value: String( a.headingLevel ),
							options: [ 2, 3, 4 ].map( ( n ) => ( { label: 'H' + n, value: String( n ) } ) ),
							onChange: ( v ) => set( { headingLevel: Number( v ) } ),
						} )
					)
				),
				preview( 'study-spots/spot-grid' )( props )
			);
		},
		save: () => null,
	} );

	registerBlockType( 'study-spots/spot-map', {
		edit( props ) {
			return el( Fragment, null,
				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Map settings', 'study-spots' ) },
						el( RangeControl, { label: __( 'Height (px)', 'study-spots' ), min: 240, max: 800, step: 20, value: props.attributes.height, onChange: ( v ) => props.setAttributes( { height: v } ) } )
					)
				),
				el( 'div', useBlockProps( { className: 'ss-map-placeholder' } ),
					el( 'strong', null, __( 'Spot Map', 'study-spots' ) ),
					el( 'p', null, __( 'Pins are generated from each spot’s latitude/longitude. Preview the page to see the live map.', 'study-spots' ) )
				)
			);
		},
		save: () => null,
	} );

	[ 'spot-meters', 'spot-facts', 'spot-stats', 'submit-form', 'spot-hero', 'vibe-tiles' ].forEach( ( n ) =>
		registerBlockType( 'study-spots/' + n, { edit: preview( 'study-spots/' + n ), save: () => null } )
	);

	/* ---------- Spot details panel ---------- */
	const Panel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	if ( ! Panel || ! wp.plugins ) {
		return;
	}
	const { useSelect } = wp.data;
	const { useEntityProp } = wp.coreData;
	const fields = window.ssFields || {};

	function SpotDetails() {
		const postType = useSelect( ( s ) => s( 'core/editor' ).getCurrentPostType(), [] );
		const [ meta, setMeta ] = useEntityProp( 'postType', 'spot', 'meta' );
		if ( 'spot' !== postType || ! meta ) {
			return null;
		}
		const update = ( key, value ) => setMeta( { ...meta, [ key ]: value } );
		const controls = Object.keys( fields ).map( ( key ) => {
			const f = fields[ key ];
			if ( f.rating ) {
				return el( RangeControl, { key, label: f.label, min: 1, max: 5, value: meta[ key ] || 3, onChange: ( v ) => update( key, v ) } );
			}
			if ( 'boolean' === f.type ) {
				return el( ToggleControl, { key, label: f.label, checked: !! meta[ key ], onChange: ( v ) => update( key, v ) } );
			}
			return el( TextControl, {
				key,
				label: f.label,
				type: 'number' === f.type ? 'number' : 'ss_last_verified' === key ? 'date' : 'text',
				step: 'number' === f.type ? 'any' : undefined,
				value: meta[ key ] ?? '',
				onChange: ( v ) => update( key, 'number' === f.type ? ( v === '' ? '' : parseFloat( v ) ) : v ),
				__nextHasNoMarginBottom: true,
			} );
		} );

		const stale = meta.ss_last_verified && ( Date.now() - new Date( meta.ss_last_verified ).getTime() ) / 864e5 > 180;
		return el( Panel, { name: 'ss-spot-details', title: __( 'Spot details', 'study-spots' ), className: 'ss-spot-details' },
			stale && el( Notice, { status: 'warning', isDismissible: false }, __( 'Not verified in 6+ months. Re-check hours and details, then update the date.', 'study-spots' ) ),
			...controls
		);
	}
	wp.plugins.registerPlugin( 'ss-spot-details', { render: SpotDetails, icon: 'location-alt' } );
} )( window.wp );

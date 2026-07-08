/**
 * Reading Time block — editor-side registration.
 *
 * Written in plain ES5-ish JS against the wp.* globals rather than
 * ESNext + JSX, so it runs directly with zero build step (no webpack,
 * no @wordpress/scripts). Trade-off: slightly more verbose createElement
 * calls instead of JSX, in exchange for a plugin that's just plain files.
 */
( function ( blocks, element, blockEditor, i18n, serverSideRender, components ) {
	var el = element.createElement;
	var __ = i18n.__;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var ServerSideRender = serverSideRender;

	blocks.registerBlockType( 'reading-time/badge', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Reading Time Settings', 'reading-time-plugin' ) },
						el( TextControl, {
							label: __( 'Custom words-per-minute (optional)', 'reading-time-plugin' ),
							help: __( 'Leave at 0 to use the global setting from Settings > Reading Time.', 'reading-time-plugin' ),
							type: 'number',
							value: attributes.customWpm,
							onChange: function ( value ) {
								setAttributes( { customWpm: parseInt( value, 10 ) || 0 } );
							},
						} )
					)
				),
				el( ServerSideRender, {
					block: 'reading-time/badge',
					attributes: attributes,
				} )
			);
		},

		// Dynamic block: nothing is saved into post_content, PHP render_callback handles output.
		save: function () {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.i18n,
	window.wp.serverSideRender,
	window.wp.components
);

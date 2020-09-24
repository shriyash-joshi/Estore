/* global wc_price_based_country_pro_dynamic_pricing_params */
jQuery( function( $ ) {
	'use strict';

	/**
	 * Dynamic pricing pricing group actions.
	 */
	var wcpbc_dynamic_pricing = {

		/**
		 * Pricing rule row template.
		 */
		rule_row_template: null,

		/**
		 * Init actions.
		 */
		init: function() {
			this.rule_row_template = wp.template( 'wcpbc-pricing-rule-row' );
			this.pricing_rule_rows();

			$(document).ready(function () {
				// Add the event handlers.
				$('#woocommerce-pricing-rules-wrap').on( 'click', '.delete_pricing_rule, .delete_pricing_blockrule', wcpbc_dynamic_pricing.on_delete_pricing_rule_click );

				$('#woocommerce-pricing-rules-wrap').on( 'click', '.add_pricing_rule, .add_pricing_blockrule', wcpbc_dynamic_pricing.on_add_pricing_rule_click );

				$('#woocommerce-pricing-rules-wrap').on( 'change', 'tr td select[name$="[type]"]', function() {
					wcpbc_dynamic_pricing.show_hide_pricing_rows( this );
				} );

				$('#woocommerce-pricing-rules-wrap').on( 'change', '.wcpbc_pricing_rules_price_method', function() {
					wcpbc_dynamic_pricing.disable_pricing_zone_amount( this );
				} );

				// Add a nonce field.
				$('#woocommerce-pricing-category form').append(
					'<input type="hidden" name="wcpbc-advanced-category-nonce" value="' + wc_price_based_country_pro_dynamic_pricing_params.security + '" />'
				)

			} );

			$(document).ajaxComplete( this.on_add_ruleset );
		},

		/**
		 * Add the pricing zone rows foreach dynamic pricing rule.
		 */
		pricing_rule_rows: function() {
			$('div.woocommerce_pricing_ruleset tr[id^="pricing_rule_row_set"], div.woocommerce_pricing_ruleset tr[id^="pricing_blockrule_row_set"]').not('.wcpbc_pricing_rules_rows_added').each( function() {
				wcpbc_dynamic_pricing.add_rule_rows( this );
			});

			$('#woocommerce-pricing-rules-wrap tr td select[name$="[type]"]').each( function(){
				wcpbc_dynamic_pricing.show_hide_pricing_rows( this );
			});
		},

		get_row_properties: function( row ) {
			var parts = $(row).attr('id').split('_row_set_');
			var last_index = parts[1].lastIndexOf('_');
			var props = {};

			props.mode = parts[0].replace( 'pricing_', '' );
			props.id    = 'set_' + parts[1].substr( 0, last_index );
			props.index = parts[1].substr( last_index+1 );

			return props;
		},

		/**
		 * Add pricing zones rows for managing.
		 */
		add_rule_rows: function( row_element ) {
			var row_props = wcpbc_dynamic_pricing.get_row_properties(row_element);
			var data;
			var field_id = ''
			var html     = '';

			wc_price_based_country_pro_dynamic_pricing_params.zones.forEach( function( element ) {
				html = wcpbc_dynamic_pricing.rule_row_template({
					"rule_id"   : row_props.id,
					"rule_mode" : row_props.mode,
					"index"     : row_props.index,
					"zone_id"   : element.zone_id,
					"label"     : element.label
				});

				$(row_element).after( html );
				$(row_element).addClass( 'wcpbc_pricing_rules_rows_added' );

				// Set the values.
				data = element.pricing_rules;

				['price_method', 'amount'].forEach( function( field_name ){
					field_id = '#wcpbc_pricing_rules_' + element.zone_id + '_' + row_props.id + '_' + row_props.mode + 's_' + row_props.index + '_' + field_name;

					if ( typeof data[row_props.id] !== 'undefined' && typeof data[row_props.id][row_props.mode + 's'] !== 'undefined' && typeof data[row_props.id][row_props.mode + 's'][row_props.index] !== 'undefined' && typeof data[row_props.id][row_props.mode + 's'][row_props.index][field_name] !== 'undefined' ) {
						$( field_id ).val( data[row_props.id][row_props.mode + 's'][row_props.index][field_name] );
					}

					if ( 'price_method' === field_name ) {
						// Disable or enabled the amount field
						wcpbc_dynamic_pricing.disable_pricing_zone_amount( field_id );
					}
				} );
			});
		},

		/**
		 * Hide zone pricing rows for percent rules type.
		 */
		show_hide_pricing_rows: function( element ) {
			var type      = $(element).val();
			var row_class = '.wcpbc_' + $(element).closest('tr').attr('id');
			$(row_class).toggle( 'percentage_discount' !== type && 'percent_adjustment' !== type );
		},

		/**
		 * Handle the disable/enable amount field of the pricing zone.
		 */
		disable_pricing_zone_amount: function( element ) {
			var amount_field_id = '#' + $(element).attr('id').replace('_price_method', '_amount');
			$(amount_field_id).toggle( 'exchange_rate' !== $(element).val() );
		},

		/**
		 * Delete rule rows.
		 */
		on_delete_pricing_rule_click: function() {
			var rule_id = $(this).data('name');
			var index   = $(this).data('index');
			var mode    = $(this).attr('class').split('_')[2];

			var row_id       = 'pricing_' + mode + '_row_' + rule_id + '_' + index;

			if ( 0 == $( '#' + row_id ).length ) {
				wc_price_based_country_pro_dynamic_pricing_params.zones.forEach( function( element ) {
					$( '#' + element.zone_id + '_' + row_id ).remove();
				});
			}
		},

		/**
		 * On click add_pricing_rule button.
		 */
		on_add_pricing_rule_click: function() {
			var rule_id = $(this).data('name');
			var index   = $(this).data('index');
			var mode    = $(this).attr('class').split('_')[2];

			var last_index  = $(this).closest('table').data( 'lastindex' );
			var row_id      = 'pricing_' + mode + '_row_' + rule_id + '_';
			var last_row_id = '#' + row_id + last_index;
			var wcpbc_class = '.wcpbc_' + row_id + index;

			$( last_row_id ).insertAfter( $( wcpbc_class ).last() );

			wcpbc_dynamic_pricing.add_rule_rows( last_row_id );
		},

		/**
		 * Add the pricing zone rows after adding new ruleset.
		 */
		on_add_ruleset: function( event, xhr, settings ) {
			if ( typeof settings.data !== 'undefined' && ( -1 < settings.data.indexOf('action=create_empty_ruleset') || -1 < settings.data.indexOf('create_empty_category_ruleset' ) ) ) {
				wcpbc_dynamic_pricing.pricing_rule_rows();
			}
		}
	};

	wcpbc_dynamic_pricing.init();
} );

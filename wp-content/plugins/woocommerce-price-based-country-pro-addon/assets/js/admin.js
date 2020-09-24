/* global wc_price_based_country_pro_admin_param */
jQuery( function( $ ) {
	'use strict';

	/**
	 * Metaboxes actions
	 */
	var wcpbc_meta_boxes = {

		/**
		 * Initialize metabox actions
		 */
		init: function() {
			$( document.body ).on( 'wc_price_based_country_manual_price_show', this.manual_price_show );
			$( 'select#product-type' ).on( 'change', this.show_and_hide_panels );
			$( 'input#_nyp' ).on( 'change', this.show_and_hide_panels );
			$( '#woocommerce-product-data' ).on( 'change', 'input.variation_is_nyp', this.show_and_hide_variation_panels );
			$('#general_coupon_data #discount_type').on('change', this.coupon_type_change );
			$( document.body ).ready( this.coupon_type_change );
			$('select.variation_actions').on( 'wcpbc_variable_bluck_edit_ajax_data', this.variable_bluck_edit );

			this.subscriptions_init();
			this.bookings_init();
			this.addons_init();
			this.german_market_init();
			this.germanized.init();
		},

		/**
		 * Show and hide pricing controls.
		 */
		show_and_hide_panels: function() {
			$(document.body).trigger( 'wc_price_based_country_show_and_hide_panels' );
		},

		show_and_hide_variation_panels: function() {
			$(this).closest('.woocommerce_variation').find( '.wcpbc_price_method[type="radio"][value="manual"]' ).each( function(){
				var show     = $(this).prop( 'checked' );
				var $wrapper = $( this ).closest( '.wcpbc_pricing' );
				if ( show ) {
					wcpbc_meta_boxes.manual_price_show( false, $wrapper );
				}
			});
		},

		/**
		 * When manual price set.
		 *
		 * @param {*} e
		 * @param {*} $wrapper
		 */
		manual_price_show: function( e, $wrapper ){
			var product_type      = $( 'select#product-type' ).val();
			var is_date_default   = ! $wrapper.find( '.wcpbc_sale_price_dates[type="radio"][value="default"]').first().prop( 'checked' );
			var is_nyp            = wc_price_based_country_pro_admin_param.name_your_price_support && ( $( 'input#_nyp' ).prop( 'checked' ) );
			var is_subscription   = wc_price_based_country_pro_admin_param.subscription_support  && ( product_type === 'subscription' || product_type === 'variable-subscription' );

			if ( wc_price_based_country_pro_admin_param.name_your_price_support ) {
				if ( $wrapper.closest('.woocommerce_variation').length ) {
					is_nyp = $wrapper.closest( '.woocommerce_variation' ).find( 'input.variation_is_nyp' ).first().prop( 'checked' );
				}
				// Name your price.
				$wrapper.find('.wcpbc_show_if_nyp').toggle( is_nyp );
				$wrapper.find('.wcpbc_show_if_manual').not('.wcpbc_show_if_nyp').toggle( ! is_nyp );
				$wrapper.find('.wcpbc_show_if_manual.wcpbc_hide_if_sale_dates_default').not('.wcpbc_show_if_nyp').toggle( ! is_nyp && is_date_default );
				if ( is_nyp ) {
					$wrapper.find('.wcpbc_input_subscription_price').prop( 'disabled', true ).css( 'background','#ccc' );
				} else {
					$wrapper.find('.wcpbc_input_subscription_price').prop( 'disabled', false ).css( 'background','#fff' );
				}
			}

			if ( wc_price_based_country_pro_admin_param.subscription_support ) {
				// Subscriptions.
				$wrapper.find( '.wcpbc_show_if_manual_subscription' ).toggle( is_subscription );
				if ( is_subscription ) {
					$wrapper.find('._regular_price_wcpbc_field, ._variable_regular_price_wcpbc_field').hide();
				}
				$wrapper.find('._variable_sale_price_wcpbc_field').toggleClass( 'form-row-last', ! is_subscription );
			}
		},

		/**
		 * Coupon type change.
		 */
		coupon_type_change: function(){
			var is_percent = $.inArray( $('#general_coupon_data #discount_type').val(), ['percent', 'sign_up_fee_percent', 'recurring_percent'] );
			$('#general_coupon_data .options_group.wcpbc_pricing').toggle( is_percent < 0 );
		},

		/**
		 * WooCommerce Subscriptions integration.
		 */
		subscriptions_init: function() {
			if ( ! wc_price_based_country_pro_admin_param.subscription_support ) {
				return;
			}

			$( document.body ).ready( this.move_subscriptions_fields );
			$( document.body ).on( 'woocommerce_variations_added', this.move_subscriptions_fields );
			$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', this.move_subscriptions_fields );

			// Update hidden regular price when subscription price is updated.
			$('#woocommerce-product-data').on('change', '.wcpbc_pricing .wcpbc_input_subscription_price', function() {
				var regular_price_sel = '#' + $(this).attr('id').replace( '_subscription_price', '_regular_price' );
				$( regular_price_sel ).val( $(this).val() );
			});
		},

		/**
		 * Move subscriptions fields.
		 */
		move_subscriptions_fields: function() {
			$('#woocommerce-product-data .wcpbc_show_if_manual_subscription').not('wcs_moved').each( function(){
				$(this).closest( '.wcpbc_pricing').find( '._variable_regular_price_wcpbc_field' ).before( $(this) );
				$(this).closest( '.wcpbc_pricing').find( '._regular_price_wcpbc_field' ).before( $(this) );
				$(this).addClass( 'wcs_moved' );
			});
		},

		/**
		 * WooCommerce Bookings integration.
		 */
		bookings_init: function() {

			$('#pricing_rows').on( 'change', '.wc_booking_pricing_type select', function( event ){
				if ( event.originalEvent !== undefined ) {
					wcpbc_meta_boxes.booking_pricing_rows_change();
				}
			});

			$('#bookings_resources').on('change', '.booking_resource_price_method', function() {
				var visible = $(this).val() == 'exchange_rate' ? 'hidden' : 'visible';
				$(this).closest('tr').find('.booking_resource_cost').css('visibility', visible );
			});

			$('#bookings_persons').on('change', '.booking_person_price_method', function() {
				var visible = $(this).val() == 'exchange_rate' ? 'hidden' : 'visible';
				$(this).closest('tr').find('.booking_person_cost').css('visibility', visible );
			});

			// Move tab under booking cost
			$('li.wcpbc_bookings_pricing_options.wcpbc_bookings_pricing_tab.show_if_booking').insertAfter('li.bookings_pricing_options.bookings_pricing_tab.show_if_booking');
			$('li.wcpbc_accommodation_bookings_rates_tab.show_if_accommodation-booking').insertAfter('li.accommodation_bookings_pricing_tab.show_if_accommodation-booking');
		},

		/**
		 * WooCommerce Bookings price row change.
		 */
		booking_pricing_rows_change: function() {
			$('#wcpbc_bookings_pricing .table_grid').hide();
			$('#wcpbc_bookings_pricing .pricing_warning').show();
		},

		/**
		 * WooCommerce Product Add-ons integation.
		 */
		addons_init: function() {
			$('#wcpbc_product_addons_data select.wcpbc-price-method').on( 'change', function(){
				var $wc_pao_addon = $(this).closest('div.wcpbc-pao-addon');
				var value = $(this).val();
				$wc_pao_addon.toggleClass('closed', 'manual' !== value );
			});

			$('#product_addons_data').on( 'change', 'select.wc-pao-addon-type-select, .wc-pao-addon-option-price-type, .wc-pao-addon-content-price input, .wc-pao-addon-adjust-price, .wc-pao-addon-adjust-price-select, .wc-pao-addon-adjust-price-value, .wc-pao-addon-min-max input', function( event ){
				if ( typeof event.isTrigger === 'undefined' ) {
					$('#wcpbc_product_addons_data .wc-metaboxes').hide();
					$('#wcpbc-addons-update-required').show();
				}
			});
		},

		/**
		 * WooCommerce German Market integration.
		 */
		german_market_init: function() {
			$('<div class="options_group"></div>').appendTo('#woocommerce-product-data #price_per_unit_options');
			$('#woocommerce-product-data #price_per_unit_options p.form-field').appendTo('#woocommerce-product-data #price_per_unit_options .options_group');
			$('.wcpbc_price_per_unit_options').appendTo('#woocommerce-product-data #price_per_unit_options');
		},

		/**
		 * Germanized for WooCommerce integration
		 */
		germanized: {
			init: function() {
				$( document.body ).on( 'woocommerce_variations_added', wcpbc_meta_boxes.germanized.move_pricing_fields );
				$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', wcpbc_meta_boxes.germanized.move_pricing_fields );
				$( document.body ).on( 'click', '#_unit_price_auto', wcpbc_meta_boxes.germanized.enable_disable_unit_price_fields );
				$( document ).ready( function() {
					$( '#general_product_data #_unit_price_auto' ).each( wcpbc_meta_boxes.germanized.enable_disable_unit_price_fields );
				} );

			},
			move_pricing_fields: function() {
				$('.woocommerce_variation.wc-metabox .variable_gzd_ts_labels').each( function() {
					$(this).closest('.woocommerce_variation.wc-metabox').find('.wcpbc_pricing').insertAfter( $(this) );
				} );
			},
			enable_disable_unit_price_fields: function() {
				if ( $( this ).is( ':checked' ) ) {
					$(this).closest('#general_product_data').find('._unit_price_regular_wcpbc_field input').attr( 'readonly', 'readonly' );
					$(this).closest('#general_product_data').find('._unit_price_sale_wcpbc_field input').attr( 'readonly', 'readonly' );
				} else {
					$(this).closest('#general_product_data').find('._unit_price_regular_wcpbc_field input').removeAttr( 'readonly' );
					$(this).closest('#general_product_data').find('._unit_price_sale_wcpbc_field input').removeAttr( 'readonly' );
				}
			}
		},

		/**
		 * Variation bulk edit.
		 */
		variable_bluck_edit: function() {
			var value;
			var data = {
				action:  $('select.variation_actions option:selected').data('action'),
				zone_id: $('select.variation_actions option:selected').data('zone-id'),
			};

			switch ( data.action ) {
				case 'regular_price_increase' :
				case 'regular_price_decrease' :
				case 'sale_price_increase' :
				case 'sale_price_decrease' :
					value = window.prompt( wc_price_based_country_pro_admin_param.i18n_enter_a_value_fixed_or_percent );

					if ( value != null ) {
						if ( value.indexOf( '%' ) >= 0 ) {
							data.value = accounting.unformat( value.replace( /\%/, '' ), woocommerce_admin.mon_decimal_point ) + '%';
						} else {
							data.value = accounting.unformat( value, woocommerce_admin.mon_decimal_point );
						}
					}
					break;
				case 'regular_price' :
				case 'sale_price' :
					value = window.prompt( wc_price_based_country_pro_admin_param.i18n_enter_a_value );

					if ( value != null ) {
						data.value = value;
					}
					break;
			}

			return data;
		}
	};

	// Order actions
	var wcpbc_meta_boxes_order_items = {
		init: function(){
			$( '#woocommerce-order-items' ).on( 'click', 'button.wcpbc-load-pricing-action', this.load_country_pricing );
		},

		block: function(){
			$( '#woocommerce-order-items' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		refresh: function(data){
			//replace content
			$( '#woocommerce-order-items' ).find( '.inside' ).empty();
			$( '#woocommerce-order-items' ).find( '.inside' ).append( data );

			// init tiptip
			$( '#tiptip_holder' ).removeAttr( 'style' );
			$( '#tiptip_arrow' ).removeAttr( 'style' );
			$( '.tips' ).tipTip({
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 200
			});

			//unblock
			$( '#woocommerce-order-items' ).unblock();

			//stupidtable init
			$( '.woocommerce_order_items' ).stupidtable();
			$( '.woocommerce_order_items' ).on( 'aftertablesort', this.add_arrows );
		},

		add_arrows: function( event, data ) {
			var th    = $( this ).find( 'th' );
			var arrow = data.direction === 'asc' ? '&uarr;' : '&darr;';
			var index = data.column;
			th.find( '.wc-arrow' ).remove();
			th.eq( index ).append( '<span class="wc-arrow">' + arrow + '</span>' );
		},

		load_country_pricing: function(){

			if ( typeof woocommerce_admin_meta_boxes === 'undefined' ) {
				return;
			}

			if ( ! window.confirm( wc_price_based_country_pro_admin_param.i18n_load_country_pricing_confirm ) ) {
				return;
			}

			// get tax args
			var tax_args = {
				country: '',
				state:'',
				postcode: '',
				city: ''
			}

			if ( 'shipping' === woocommerce_admin_meta_boxes.tax_based_on ) {
				tax_args.country  = $( '#_shipping_country' ).val();
				tax_args.state    = $( '#_shipping_state' ).val();
				tax_args.postcode = $( '#_shipping_postcode' ).val();
				tax_args.city     = $( '#_shipping_city' ).val();
			}

			if ( 'billing' === woocommerce_admin_meta_boxes.tax_based_on || ! tax_args.country ) {
				tax_args.country  = $( '#_billing_country' ).val();
				tax_args.state    = $( '#_billing_state' ).val();
				tax_args.postcode = $( '#_billing_postcode' ).val();
				tax_args.city     = $( '#_billing_city' ).val();
			}

			var data = {
				action:   		 'wc_price_based_country_load_country_pricing',
				order_id: 		  woocommerce_admin_meta_boxes.post_id,
				billing_country:  $( '#_billing_country' ).val(),
				shipping_country: $( '#_shipping_country' ).val(),
				items:    		  $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize(),
				tax_args: 		  tax_args,
				security: 		  wc_price_based_country_pro_admin_param.load_country_pricing_nonce
			};

			wcpbc_meta_boxes_order_items.block();

			$.ajax({
				url:  wc_price_based_country_pro_admin_param.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					wcpbc_meta_boxes_order_items.refresh(response);
				}

			});
		}
	};

	var wcpbc_settings = {

		/**
		 * Init
		 */
		init: function() {
			$('#woocommerce_currency_pos').closest('tr').hide();

			$('.wcpbc-zone-settings #exchange_rate').closest('tr').insertAfter( $('input[name="auto_exchange_rate"]').closest('tr') );

			$('#exchange_rate').closest('tr').toggle( ( $('input[name="auto_exchange_rate"]:checked').val() == 'no' ) );
			$('#exchange_rate_fee').closest('tr').toggle( $('input[name="auto_exchange_rate"]:checked').val() == 'yes' );

			$('input[name="auto_exchange_rate"]').on( 'click', function(){
				$('#exchange_rate').closest('tr').toggle( $(this).val() == 'no' );
				$('#exchange_rate_fee').closest('tr').toggle( $(this).val() == 'yes' );
			});

			if ( $( '#wc_price_based_currency_format_preview' ).length > 0 ){
				$('#woocommerce_currency').addClass('wc_price_based_country_preview_currency');
				$('.settings-panel.wcpbc-zone-settings select[name="currency"]').addClass('wc_price_based_country_preview_currency');
				$('#wc_price_based_currency_format').addClass('wc_price_based_country_preview_format');
				$('#woocommerce_price_num_decimals').addClass('wc_price_based_country_preview_num_decimals');
				$('#woocommerce_price_decimal_sep').addClass('wc_price_based_country_preview_decimal_sep');
				this.price_preview();
			}

			$('.wc_price_based_country_preview_currency, .wc_price_based_country_preview_format, .wc_price_based_country_preview_num_decimals, .wc_price_based_country_preview_decimal_sep').on('change', function(){
				wcpbc_settings.price_preview();
			});

			$('select#wc_price_based_country_exchange_rate_api').on('change', this.show_if_exchange_rate);
			this.show_if_exchange_rate();
		},

		/**
		 * Price preview function.
		 */
		price_preview: function() {
			var symbol         = $('.wc_price_based_country_preview_currency:first option:selected').text();
			var code           = $('.wc_price_based_country_preview_currency:first option:selected').val();
			var currencyFormat = $('.wc_price_based_country_preview_format:first').val();
			if ( ! currencyFormat ) {
				currencyFormat = $('#wc_price_based_currency_format_preview').data('default');
			}
			var num_decimals   = parseInt( $('.wc_price_based_country_preview_num_decimals:first').val() );
			var decimal_sep    = $('.wc_price_based_country_preview_decimal_sep:first').val();
			var price_preview  = '99' + (num_decimals > 0 ? decimal_sep : '' ) + '9'.repeat(num_decimals);
			var symbolPos      = symbol.lastIndexOf('(');
			if (symbolPos>-1) {
				symbol = symbol.substr(symbolPos+1).replace(')', '');
				if ( currencyFormat.indexOf('[price]') < 0 ) {
					currencyFormat = currencyFormat + '[price]';
				}

				var symbol_alt     = typeof wc_price_based_country_pro_admin_param.alt_currency_symbols[code] === 'undefined' ? symbol : wc_price_based_country_pro_admin_param.alt_currency_symbols[code];

				var currencyPreview = currencyFormat.replace('[price]', price_preview);
				currencyPreview = currencyPreview.replace('[code]', code);
				currencyPreview = currencyPreview.replace('[symbol]', symbol );
				currencyPreview = currencyPreview.replace('[symbol-alt]', symbol_alt );
				$('#wc_price_based_currency_format_preview').html(currencyPreview);
			}
		},

		/**
		 * Exchange rate options visibility.
		 */
		show_if_exchange_rate: function() {
			var val = $('select#wc_price_based_country_exchange_rate_api').val();
			if ( typeof val !== 'undefined' ) {
				$('.wcpbc_exchange_rate_option').closest('tr').hide();
				$('.wcpbc_exchange_rate_option.show_if_exchange_rate_' + val).closest('tr').show();
			}
		}
	};

	wcpbc_meta_boxes.init();

	if ( typeof woocommerce_admin_meta_boxes !== 'undefined' ) {
		wcpbc_meta_boxes_order_items.init();
	}

	wcpbc_settings.init();

});
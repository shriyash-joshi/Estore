/** global wc_price_based_country_admin_system_report_params */
jQuery( function( $ ) {
	'use strict';

	var geolocation_debug = {

		/**
		 * Return a geolocation debug variable.
		 *
		 * @param {string} var_name
		 * @return {mixed}
		 */
		get_geo_var: function( var_name ) {
			var selector = '#wcpbc-' + var_name;
			var value    = false;
			if ( typeof $( selector ).data('value') !== 'undefined' && $( selector ).data('value') ) {
				value = $( selector ).data('value');
				if ( value.indexOf(',' ) > -1 ) {
					value = value.split(',').shift().trim();
				}
			}
			return value;
		},

		/**
		 * If definded a variable that contains the country.
		 *
		 * @return {bool}
		 */
		isset_geo_country: function() {
			return geolocation_debug.get_geo_var( 'mm-country-code' ) || geolocation_debug.get_geo_var( 'http-cf-ipcountry' ) || geolocation_debug.get_geo_var( 'geoip-country-code' ) || geolocation_debug.get_geo_var( 'http-x-country-code' );
		},

		/**
		 * There is the x_forwarded-for issue?
		 *
		 * @return {bool}
		 */
		x_forwarded_for_issue: function() {
			return ! geolocation_debug.isset_geo_country() && ! geolocation_debug.get_geo_var( 'http-x-real-ip' ) && geolocation_debug.get_geo_var( 'http-x-forwarded-for' ) && geolocation_debug.get_geo_var( 'http-x-forwarded-for' ) !== geolocation_debug.get_geo_var( 'remote-addr' );
		},

		/**
		 * First IP not empty.
		 */
		get_first_ip_no_empty: function() {
			var server_vars = ['http-x-real-ip', 'http-x-forwarded-for', 'remote-addr'];
			var value       = false;;
			server_vars.forEach( function( server_var ){
				var ip = geolocation_debug.get_geo_var( server_var );
				if ( false === value && false !== ip) {
					value = ip;
				}
			});

			return value;
		},

		/**
		 * Running a test to check if the REMOTE_ADDR IP country match with the real IP country.
		 */
		remote_addr_test: function() {
			var real_ip = geolocation_debug.get_geo_var('real-external-ip');

			$.post( wc_price_based_country_admin_system_report_params.ajax_url, {
				action:      'wc_price_based_country_remote_addr_check',
				security:    wc_price_based_country_admin_system_report_params.remote_addr_check_nonce,
				external_ip: real_ip
			}, function( response ){
				if ( '1' == response.result ) {
					if ( geolocation_debug.get_geo_var('use-remote-addr') ) {
						geolocation_debug.append_result( 'ok' );
					} else {
						geolocation_debug.append_result( 'fail', wc_price_based_country_admin_system_report_params.define_constant_alert );
					}
				} else {
					geolocation_debug.append_result( 'fail' );
				}
			});
		},

		/**
		 * Running a test to check if first IP country match with the real IP country.
		 */
		first_ip_test: function() {
			var real_ip  = geolocation_debug.get_geo_var('real-external-ip');
			var first_ip = geolocation_debug.get_first_ip_no_empty();

			$.post( wc_price_based_country_admin_system_report_params.ajax_url, {
				action:      'wc_price_based_country_remote_addr_check',
				security:    wc_price_based_country_admin_system_report_params.remote_addr_check_nonce,
				external_ip: real_ip,
				remote_addr: first_ip
			}, function( response ){
				if ( '1' == response.result ) {
					geolocation_debug.append_result( 'ok' );
				} else {
					geolocation_debug.append_result( 'fail', wc_price_based_country_admin_system_report_params.ip_no_match );
				}
			});
		},

		/**
		 * Append the result of the text.
		 *
		 * @param {string} result
		 * @param {string} message
		 */
		append_result: function(result, message) {
			var text = '';
			message = 'undefined' === typeof message ? '' : message;

			switch ( result ) {
				case 'ok':
					text = '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
					break;
				case 'fail':
					text = '<mark class="error"><span class="dashicons dashicons-warning"></span>' + message + '</mark>';
					break;
			}
			$('#wcpbc-geolocation-test td.wcpbc-geolocation-test-result').html( text );
		},

		/**
		 * Run the geolocation test.
		 */
		geolocation_test: function() {
			if ( ! geolocation_debug.isset_geo_country() && 'yes' !== geolocation_debug.get_geo_var('geoipdb-exists') ) {
				geolocation_debug.append_result( 'fail', wc_price_based_country_admin_system_report_params.geoipdb_required );
			} else if ( geolocation_debug.x_forwarded_for_issue() ) {
				if ( geolocation_debug.get_geo_var('remote-addr') === geolocation_debug.get_geo_var('real-external-ip') && ! geolocation_debug.get_geo_var('use-remote-addr') ) {
					// append fix error geolocation.
					geolocation_debug.append_result( 'fail', wc_price_based_country_admin_system_report_params.define_constant_alert );
				} else {
					geolocation_debug.remote_addr_test();
				}
			} else if ( ! geolocation_debug.isset_geo_country() && geolocation_debug.get_first_ip_no_empty() !== geolocation_debug.get_geo_var('real-external-ip') ) {
				geolocation_debug.first_ip_test();
			} else {
				// Result ok.
				geolocation_debug.append_result('ok');
			}
		},

		init: function(){
			$( '#wcpbc-geolocation-debug' ).on( 'wc_price_based_country_real_external_ip_loaded', function() {
				geolocation_debug.geolocation_test();
			});
		}
	};

	geolocation_debug.init();
});

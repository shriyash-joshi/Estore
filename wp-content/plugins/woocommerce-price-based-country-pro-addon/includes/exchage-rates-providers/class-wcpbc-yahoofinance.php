<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCPBC_YahooFinance' ) ) :

/**
 *
 * @class WCPBC_FloatRates
 * @version	2.1.7
 */
class WCPBC_YahooFinance extends WCPBC_Exchange_Rates_Provider {	
	
	/**
	 * Constructor
	 */
	public function __construct() {		
		$this->name = 'Yahoo Finance';		
	}

	/**
	 * Return API endpoint
	 *
	 * @param  string $from_currency
	 * @param  array $to_currency	 
	 * @return string
	 */
	protected function get_api_endpoint(){
		$pairs = array();
		foreach ( $this->to_currency as $currency ) {
			$pairs[] = urlencode( '"' . $this->from_currency . $currency . '"' );
		}
			
		return 'https://query.yahooapis.com/v1/public/yql?q=select * from yahoo.finance.xchange where pair in ('. implode(',', $pairs ) . ')&env=store://datatables.org/alltableswithkeys&format=json';
	}

	/**
	 * Return rates array from response
	 *
	 * @param string $data
	 * @return array
	 */
	protected function parse_response( $data ){		

		$rates = array();

		$data = json_decode( $data );						

		if ( is_array( $data->query->results->rate ) ) {
			$query_rates = $data->query->results->rate;
		} else {
			$query_rates = array( $data->query->results->rate );
		}
				
		foreach ( $query_rates as $rate ) {
			$currency = str_replace( $this->from_currency, '', $rate->id );
			$rates[ $currency ] = $rate->Rate;
		}		

		return $rates;
	}
	
}

endif;

return new WCPBC_YahooFinance();
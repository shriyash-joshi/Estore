<?php
/**
 * Product price exchange rate update process.
 *
 * @package WCPBC
 * @since   1.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Background_Process', false ) && defined( 'WC_PLUGIN_FILE' ) ) {
	include_once dirname( WC_PLUGIN_FILE ) . '/includes/abstracts/class-wc-background-process.php';
}

/**
 * WCPBC_Product_Sync_Background class.
 */
class WCPBC_Product_Sync_Background extends WC_Background_Process {

	/**
	 * Initiate new background process.
	 */
	public function __construct() {
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'wcpbc_' . get_current_blog_id();
		$this->action = 'pricesync';

		// Dispatch queue after shutdown.
		add_action( 'shutdown', array( $this, 'dispatch_queue' ), 50 );

		parent::__construct();
	}

	/**
	 * Push to queue
	 *
	 * @param mixed $data Data.
	 *
	 * @return $this
	 */
	public function push_to_queue( $data ) {
		$grouped = false;

		if ( isset( $data['task'] ) && 'parent_product_price_sync' === $data['task'] && isset( $data['zone'] ) && isset( $data['type'] ) && 'variable' === $data['type'] ) {
			// Group the parent_product_price_sync tasks.
			foreach ( $this->data as $index => $item ) {

				if ( isset( $item['task'] ) && isset( $item['zone'] ) && isset( $item['type'] ) && $item['task'] === $data['task'] && $item['zone'] === $data['zone'] && $item['type'] === $data['type'] ) {

					$grouped = true;

					if ( ! empty( $data['product_ids'] ) ) {
						$product_ids         = isset( $item['product_ids'] ) ? $item['product_ids'] : array();
						$data['product_ids'] = is_array( $data['product_ids'] ) ? $data['product_ids'] : array( $data['product_ids'] );

						$this->data[ $index ]['product_ids'] = array_unique( array_merge( $product_ids, $data['product_ids'] ) );
					}

					if ( isset( $data['context'] ) && 'exchange_rate' === $data['context'] ) {
						$this->data[ $index ]['context'] = 'exchange_rate';
					}
				}
			}
		}

		if ( ! $grouped ) {
			$this->data[] = $data;
		}

		return $this;
	}

	/**
	 * Save queue and clear it.
	 *
	 * @return $this
	 */
	public function save() {
		usort( $this->data, array( __CLASS__, 'order_tasks_queue' ) );
		parent::save();
		$this->data = array();

		return $this;
	}

	/**
	 * Save and run queue.
	 */
	public function dispatch_queue() {
		if ( ! empty( $this->data ) ) {
			$this->save()->dispatch();
		}
	}

	/**
	 * Order tasks queue callback.
	 *
	 * @param array $a Element a of the array to compare.
	 * @param array $b Element b of the array to compare.
	 */
	public static function order_tasks_queue( $a, $b ) {
		if ( ! ( isset( $a['zone'] ) && isset( $b['zone'] ) && isset( $a['task'] ) && isset( $b['task'] ) ) ) {
			return 0;
		}

		$weighing = array(
			'parent_product_price_method' => 0,
			'update_exchange_rate_prices' => 1,
			'default_scheduled_sales'     => 5,
			'scheduled_sales'             => 6,
			'parent_product_price_sync'   => 10,

		);
		$wa = 1;
		$wb = 0;

		if ( $a['zone'] === $b['zone'] ) {
			if ( isset( $weighing[ $a['task'] ] ) && isset( $weighing[ $b['task'] ] ) ) {
				$wa = $weighing[ $a['task'] ];
				$wb = $weighing[ $b['task'] ];

				if ( isset( $a['metakey'] ) && isset( $b['metakey'] ) ) {
					$wa = $wa . $a['metakey'];
					$wb = $wb . $b['metakey'];
				}
			}
		} else {
			$wa = $a['zone'];
			$wb = $b['zone'];
		}

		return $wa > $wb ? 1 : -1;
	}

	/**
	 * Code to execute for each item in the queue
	 *
	 * @param string $item Queue item to iterate over.
	 * @return bool
	 */
	protected function task( $item ) {
		if ( ! $item || empty( $item['task'] ) || empty( $item['zone'] ) ) {
			return false;
		}

		$result = false;
		$limit  = 50;

		switch ( $item['task'] ) {

			case 'scheduled_sales':
				WCPBC_Product_Sync::scheduled_sales( $item['zone'] );
				break;

			case 'delete_zone_metadata':
				WCPBC_Product_Sync::delete_zone_metadata( $item['zone'] );
				break;

			case 'products_sync':
				// Improve the performance by re-queue.
				WCPBC_Product_Sync::queue_sync_exchange_rate_price( $item['zone'] );
				break;

			case 'update_zone_exchange_rate':
				WCPBC_Product_Sync::update_zone_exchange_rate( $item['zone'] );
				break;

			case 'parent_product_price_method':
				WCPBC_Product_Sync::parent_product_price_method( $item['zone'] );
				break;

			case 'update_exchange_rate_prices':
				$item['metakey'] = isset( $item['metakey'] ) ? $item['metakey'] : '';

				WCPBC_Product_Sync::update_exchange_rate_prices( $item['zone'], $item['metakey'] );
				break;

			case 'parent_product_price_sync':
				$rows = WCPBC_Product_Sync::parent_product_price_sync( $item, $limit );

				if ( $rows >= $limit ) {
					$result = $item;

				} elseif ( isset( $item['context'] ) && 'exchange_rate' === $item['context'] && isset( $item['type'] ) && 'variable' === $item['type'] ) {

					// Sync grouped after variable, because grouped can contain variables.
					$result = array(
						'task'    => 'parent_product_price_sync',
						'zone'    => $item['zone'],
						'context' => 'exchange_rate',
						'type'    => 'grouped',
						'limit'   => $limit,
					);
				} elseif ( isset( $item['context'] ) && 'exchange_rate' === $item['context'] && isset( $item['type'] ) && 'grouped' === $item['type'] ) {

					// Invalidate cache after exchange rate sync end.
					WC_Cache_Helper::get_transient_version( 'product', true );
				}

				break;
		}

		return $result;
	}
}

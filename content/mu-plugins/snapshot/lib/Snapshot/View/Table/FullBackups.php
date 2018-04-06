<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Snapshot_View_Table_FullBackups extends WP_List_Table {

	function __construct () {
		//Set parent defaults
		parent::__construct( array(
			'singular' => __( 'Archive', SNAPSHOT_I18N_DOMAIN ),     //singular name of the listed records
			'plural'   => __( 'Archive', SNAPSHOT_I18N_DOMAIN ),    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );
	}

	public function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'name' => __('File Name', SNAPSHOT_I18N_DOMAIN),
			'timestamp' => __('Date / Time', SNAPSHOT_I18N_DOMAIN),
			'size' => __('Size', SNAPSHOT_I18N_DOMAIN),
			'actions' => __('Actions', SNAPSHOT_I18N_DOMAIN),
		);
	}

	public function get_sortable_columns () {
		return array(
			'timestamp' => array('timestamp', false),
			'size' => array('size', false),
		);
	}

	public function column_cb ($item) {
		return Snapshot_Helper_Utility::current_user_can('manage_snapshots_items')
			? '<input type="checkbox" name="delete-bulk[]" value="' . (int)$item['timestamp'] . '" />'
			: ''
		;
	}

	public function column_name ($item) {
		if (empty($item['name'])) return false;
		$is_local = !empty($item['local']);
		$actions = array(
			'download' => '<a href="#download">' . esc_html(__('Download', SNAPSHOT_I18N_DOMAIN)) . '</a>',
			'restore' => '<a href="#restore">' . esc_html(__('Restore', SNAPSHOT_I18N_DOMAIN)) . '</a>',
			'trash' => '<a href="#trash">' . esc_html(__('Trash', SNAPSHOT_I18N_DOMAIN)) . '</a>',
		);
		//if ($is_local) $actions['upload'] = '<a href="#upload">' . esc_html(__('Upload', SNAPSHOT_I18N_DOMAIN)) . '</a>';
		return sprintf(
			'%1$s <small class="location %3$s"><em>(%2$s)</em></small> %4$s',
			$item['name'],
			($is_local ? __('Local', SNAPSHOT_I18N_DOMAIN) : __('Remote', SNAPSHOT_I18N_DOMAIN)),
			($is_local ? 'local' : 'remote'),
			$this->row_actions($actions)
		);
	}

	public function column_timestamp ($item) {
		if (!empty($item['timestamp']) && is_numeric($item['timestamp'])) {
			$timestamp = (int)$item['timestamp'];
			if ($timestamp) $timestamp = Snapshot_Model_Time::get()->to_local_time($timestamp);
		} else $timestamp = false;

		$time = $timestamp
			? date_i18n(get_option('time_format'), $timestamp)
			: __('N/A', SNAPSHOT_I18N_DOMAIN)
		;

		$date = $timestamp
			? '&nbsp;&middot;&nbsp;' . date_i18n(get_option('date_format'), $timestamp)
			: ''
		;
		return "{$time}{$date}";
	}

	public function column_size ($item) {
		return !empty($item['size']) && is_numeric($item['size'])
			? Snapshot_Helper_Utility::size_format((int)$item['size'])
			: ''
		;
	}

	public function column_actions ($item) {
		return '<button type="button" class="button button-primary restore"><span>' . __('Restore', SNAPSHOT_I18N_DOMAIN) . '</span></button>';
	}

	public function get_bulk_actions () {
		return array(
			'delete' => __('Delete', SNAPSHOT_I18N_DOMAIN),
		);
	}

	public function extra_tablenav ($loc) {
		if ('top' !== $loc) return false;
		$model = new Snapshot_Model_Full_Backup;
		Snapshot_View_Template::get('form')->load('monthly_filter', array('items' => $this->items, 'model' => $model));
	}


	public function prepare_items ($items = array()) {
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, array(), $sortable);

		$items = $this->_apply_table_filters($items);

		if ( count( $items ) ) {
			usort($items, array($this, 'resort_snapshots'));

			$per_page = get_user_meta( get_current_user_id(), 'snapshot_items_per_page', true );
			if ( ( ! $per_page ) || ( $per_page < 1 ) ) {
				$per_page = 20;
			}

			$current_page = $this->get_pagenum();

			if ( count( $items ) > $per_page ) {
				$this->items = array_slice( $items, ( ( $current_page - 1 ) * intval( $per_page ) ), intval( $per_page ), true );
			} else {
				$this->items = $items;
			}

			$this->set_pagination_args( array(
				'total_items' => count( $items ),
				// WE have to calculate the total number of items
				'per_page'    => intval( $per_page ),
				// WE have to determine how many items to show on a page
				'total_pages' => ceil( intval( count( $items ) ) / intval( $per_page ) )
				// WE have to calculate the total number of pages
				)
			);
		}
	}

	public function resort_snapshots ($a, $b) {
		$order_delta = !empty($_GET['order']) && 'asc' === strtolower($_GET['order'])
			? 1
			: -1
		;
		$orderby = !empty($_GET['orderby']) && 'size' === strtolower($_GET['orderby'])
			? 'size'
			: 'timestamp'
		;

		$result = ('size' === $orderby)
			? ((int)$a[$orderby] > (int)$b[$orderby] ? 1 : -1)
			: strcmp($a[$orderby], $b[$orderby])
		;

		return $result * $order_delta;
	}

	/**
	 * Filters the table items according to date.
	 *
	 * @param array $items List of items to filter
	 *
	 * @return array Filtered list
	 */
	private function _apply_table_filters ($items) {
		if (empty($_POST['snapshot-full-date_selection'])) return $items;
		$date = trim(stripslashes_deep($_POST['snapshot-full-date_selection']));

		// Skip filtering for invalid format
		if (!preg_match('/^\d{4}-\d{1,2}$/', $date)) return $items;

		$start = strtotime("{$date}-1");
		if (!$start) return $items;

		$end = strtotime("+1 months", $start) - 1;
		if (!$end || $start >= $end) return $items;

		$result = array();
		foreach ($items as $item) {
			if ($item['timestamp'] < $start) continue;
			if ($item['timestamp'] > $end) continue;
			$result[] = $item;
		}

		return $result;
	}
}
<?php

namespace Includes\ImportExport\Export;

/**
 * Export the enrolled users data.
 * @author WisdmLabs
 */
if (! class_exists('SchedulerEnrolledUsersExport')) {
    /**
    * When User Specific Data is exported in csv file.
    * Returns the data of user specific pricing for csv.
    */
    class SchedulerEnrolledUsersExport
    {
        /**
         * fetch the data form database.
         * @global object $wpdb database object.
         * @return array content for creating csv
         */
        public function fetchUsersListForProduct($productId)
        {
            global $wpdb;

            $enrlUserListTable = wdmwsReturnEnrlUserListTable();
            $headings   = array('Product id', 'User Email', 'Enrolled Date');
            $query = "SELECT product_id, user_email, enrolled_date FROM ".$enrlUserListTable." WHERE product_id = ".$productId;
            $results = $wpdb->get_results($query, ARRAY_A);

            if ($results) {
                return array($headings, $results);
            }
            return array();
        }

        /**
         * [processResult process the data to be exported]
         * @param  [array] $user_product_result [Fetched result from database]
         * @return [array] $user_product_result [Processed result]
         */
        public function processResult($user_product_result)
        {
            foreach ($user_product_result as $key => $result) {
                if ($result->discount_price == 2) {
                    $user_product_result[$key]->discount_price = $result->price;
                    $user_product_result[$key]->price = '';
                } else {
                    $user_product_result[$key]->discount_price = '' ;
                }
            }
            return $user_product_result;
        }

        /**
         * returns name of file for export
         * @return string filename
         */
        public function returnEnrlUsersListCSVName()
        {
            return '/users_list.csv';
        }
    }

}

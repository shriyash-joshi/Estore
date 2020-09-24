<?php
namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Product and enrolled Users related data on admin side.
 * @author WisdmLabs
 */
if (!class_exists('SchedulerAdminNotifyEnrolledUsers')) {
    class SchedulerAdminNotifyEnrolledUsers
    {
        /**
         * Returns the users' list enrolled for a product notification.
         */
        public $productNameList;
        public function getProductEnrolledUsersList($data)
        {
            global $wpdb;

            $productId = $data['product_id'];
            $enrlUserListTable = wdmwsReturnEnrlUserListTable();
            $limit = 'LIMIT '.$data['start'].', '.$data['length'];
            $searchKeyword = $data['search']['value'];
            $order = $data['order'][0];
            $orderByClause = 'ORDER BY '.($order['column']+2).' '.$order['dir'];

            $query = "SELECT product_id, user_email, enrolled_date FROM ".$enrlUserListTable." WHERE product_id = ".$productId. " AND user_email LIKE '%".$searchKeyword."%' ".$orderByClause." ".$limit;
            
            $results = $wpdb->get_results($query, ARRAY_A);

            $removeText = __('Remove', WDM_WOO_SCHED_TXT_DOMAIN);

            // Add remove link.
            foreach ($results as $key => $value) {
                $results[$key]['remove_link'] = '<button type="button" class="disenroll-user" name="disenroll-user" value="' . $value['product_id'] . '" data-email="'.$value['user_email'].'">' . $removeText . '</button><span class="spinner"></span>';
            }

            $queryForTotalRecords = "SELECT count(*) AS total_users FROM ".$enrlUserListTable." WHERE product_id = ".$productId. " AND user_email LIKE '%".$searchKeyword."%'";
            $totalRecords = $wpdb->get_var($queryForTotalRecords);

            return array("recordsFiltered" => $totalRecords, "data" => $results);
        }

        /**
         * Returns the number for users enrolled (user count) for a particular product.
         */
        public function getProductEnrlUsersCount($data)
        {
            global $wpdb;
            $limit = '';
            $order = $data['order'][0];
            $orderBy=" ";
            if ('1' === $order['column']) {
                $orderBy=$order['dir']=='asc'?"ASC":"DESC";
                $orderBy="ORDER BY users_count $orderBy";
                $limit = 'LIMIT '.$data['start'].', '.$data['length'];
            } elseif ('0' === $order['column']) {
                $nameOrder=$order['dir']=='asc'?"ASC":"DESC";
                $idsSortedByName=$this->getWdmwsIdsSortedByProductName($nameOrder);
                if (sizeof($idsSortedByName)>1) {
                    $orderBy="ORDER BY FIELD(".implode(', ', $idsSortedByName).")";
                }
            }
            
            // Fetch the search keyword.
            $searchKeyword = $data['search']['value'];
            $cond = '';
            $srchCond='';
            if (!empty($searchKeyword)) {
                $productIds = returnProductIdsForSearchTerm($searchKeyword, array('product', 'product_variation'));
                if (!empty($productIds)) {
                    if (!empty($idsSortedByName)) {
                        $productIds=array_intersect($productIds, $idsSortedByName);
                    }
                    $cond = "having product_id IN(".implode(', ', $productIds).")";
                    $srchCond="having product_id IN(".implode(', ', $productIds).")";
                } else {
                    $cond = "having product_id IN(0)";
                    $srchCond=$cond;
                }
            } elseif (!empty($idsSortedByName)) {
                $idsSortedByName=array_slice($idsSortedByName, $data['start'], $data['length']);
                $cond = "having product_id IN(".implode(', ', $idsSortedByName).")";
            }

            $query = "SELECT product_id, count(*) as users_count from ".$wpdb->prefix."wdmws_enrl_users GROUP BY product_id ".$cond." $orderBy ".$limit;
            $results = $wpdb->get_results($query, ARRAY_A);
            
            $url = admin_url().'admin.php?page=wdmws_settings_enrolled_users&product_id=';

            foreach ($results as $key => $value) {
                $productId = $value['product_id'];
                $product = wc_get_product($productId);
                $productTitle = $product->get_name();
                $results[$key]['product_name'] = $productTitle;
            }

            $results = $this->wdmwsSortTheEnrolledList($results, $data);

            // Adding link.
            foreach ($results as $key => $value) {
                $results[$key]['product_name'] = '<a href="' . $url . $value['product_id'] . '">' . $value['product_name'] . '</a>';
            }

            $queryForTotalRecords = "SELECT count(*) FROM (SELECT product_id from ".$wpdb->prefix."wdmws_enrl_users GROUP BY product_id ".$srchCond." ) as enrl_user_list";
            $totalRecords = $wpdb->get_var($queryForTotalRecords);

            return array("recordsFiltered" => $totalRecords, "data" => $results);
        }


        public function wdmwsSortTheEnrolledList($results, $data)
        {
            $order = $data['order'][0];
            $productNames  = array_column($results, 'product_name');
            $usrCount = array_column($results, 'users_count');
            $productNames = array_map('strtolower', $productNames);
            $usrCount = array_map('strtolower', $usrCount);
            if ('0' === $order['column']) {
                if ('asc' == $order['dir']) {
                    array_multisort($productNames, SORT_ASC, $results);
                } else {
                    array_multisort($productNames, SORT_DESC, $results);
                }
            }
            return $results;
        }

        public function getWdmwsIdsSortedByProductName($nameOrder)
        {
            global $wpdb;
            $query="SELECT DISTINCT product_id FROM ".$wpdb->prefix."wdmws_enrl_users";
            $results = $wpdb->get_col($query);
            $ids=array();
            foreach ($results as $productId) {
                $product = wc_get_product($productId);
                $productTitle = $product->get_name();
                $nameArray[] = $productTitle;
                $nameIdArray[] = ['name'=>$productTitle,'id'=>$productId];
            }
            $nameArray=array_map('strtolower', $nameArray);
            array_multisort($nameArray, SORT_ASC, $nameIdArray);
            $this->productNameList=$nameIdArray;
            $ids=array_column($nameIdArray, 'id');
            if ($nameOrder=="DESC") {
                $ids=array_reverse($ids);
            }
            return $ids;
        }
    }
}

<?php

/**
 * class to provide submission export capability in different formats.
 */
class ERForms_Submission_Export {

    protected $type;

    public function __construct($type = 'csv') {
        $this->type = $type;
    }

    public function export($form_id, $data) {
        if (empty($data) || empty($form_id))
            return false;
        
        $path= '';
        if ($this->type == 'csv') { 
            $path= $this->export_to_csv($form_id, $data);
        }
        return $path;
    }

    protected function export_to_csv($form_id, $data) {
        $form_model = erforms()->form;
        $form = $form_model->get_form($form_id);
        $file_prefix = !empty($form['title']) ? sanitize_file_name($form['title']) . '_' : $form_id . '_';

        $csv_name = $file_prefix . time() . mt_rand(10, 1000000);
        $csv_path = get_temp_dir() . $csv_name . '.csv';
        $csv = fopen($csv_path, "w");

        if (!$csv) {
            return array();
        }

        fputs($csv, chr(0xEF) . chr(0xBB) . chr(0xBF)); //UTF-8 encoding
        
        foreach ($data as $row) {
            fputcsv($csv, $row);
        }
        fclose($csv);
        return $csv_path;
    }

}
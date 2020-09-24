<?php 

class UniCommand extends CConsoleCommand{
    
    public $debug;

    public function beforeAction($action, $params) {
        parent::beforeAction($action, $params);
        $called_class = preg_replace('/Command$/i', '', get_called_class());
        
        // don't use actionIndex if you plan to run more than one action at a time
        if(strtolower($action) == 'index'){
            $command = sprintf('ps aux | grep -v grep | egrep -i "yiicmd.php\s+%s" | wc -l', $called_class);
        }else{
            $command = sprintf('ps aux | grep -v grep | egrep -i "yiicmd.php\s+%s\s+%s" | wc -l', $called_class, $action);
        }
        
        if((int)shell_exec($command) > 2){
            $this->_d($called_class + " is already running", true, true, true);
            exit(1);
        }
        return true;
    }
    
    protected function _d($message = '', $line_feed = true, $error = false, $force = false){
        if($this->debug || $force){
            $message = $line_feed ? $message . PHP_EOL : $message;
            $error ? fwrite(STDERR, $message) : fwrite(STDOUT, $message);
        }
    }

}
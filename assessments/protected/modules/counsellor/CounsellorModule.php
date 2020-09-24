<?php

class CounsellorModule extends CWebModule {

    public function init() {

        $this->setImport(array(
            'counsellor.models.*',
            'counsellor.controllers.AbstractCounsellorController',
            'application.models.*',
        ));
    }

}

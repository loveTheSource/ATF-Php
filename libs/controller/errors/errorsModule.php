<?php

namespace ATFApp\Controller\Errors;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

class ErrorsModule extends \ATFApp\Controller\BaseModule {

    public function getModuleData() {
        return [
            'header' => BasicFunctions::getLangText('basics', 'err_header')
        ];
    }
}
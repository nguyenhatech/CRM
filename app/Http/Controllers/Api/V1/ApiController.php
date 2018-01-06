<?php

namespace Nh\Http\Controllers\Api\V1;

use Nh\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected $validationRules = [];
    protected $validationMessages = [];

    public function checkPermission($moduleName)
    {
        $this->middleware("ability:superadmin,{$moduleName}.index")->only(['index']);
        $this->middleware("ability:superadmin,{$moduleName}.show")->only('show');
        $this->middleware("ability:superadmin,{$moduleName}.store")->only('store');
        $this->middleware("ability:superadmin,{$moduleName}.update")->only('update');
        $this->middleware("ability:superadmin,{$moduleName}.destroy")->only('destroy');
    }
}

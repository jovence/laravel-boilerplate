<?php

namespace App\Http\Controllers\Api;

use App\Base\BaseController;

class ExampleController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(
            data: ['example' => 'This is an example response'],
            message: 'Example data retrieved successfully'
        );
    }
}

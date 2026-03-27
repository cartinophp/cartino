<?php

namespace Cartino\Http\Controllers;

use Cartino\Http\Controllers\Cp\Concerns\HandlesFlashMessages;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, HandlesFlashMessages, ValidatesRequests;
}

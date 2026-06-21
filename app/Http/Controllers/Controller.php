<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function idsMatch($left, $right): bool
    {
        if ($left === null || $right === null || $left === '' || $right === '') {
            return false;
        }

        if (!is_numeric($left) || !is_numeric($right)) {
            return false;
        }

        return (int) $left === (int) $right;
    }
}

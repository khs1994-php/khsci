<?php

declare(strict_types=1);

namespace App\Http\Controllers\Test;

use KhsCI\Support\Session;

class TestController
{
    public function __invoke()
    {
        Session::put('user.name', 1);
        Session::get('user.name');
        var_dump(Session::get('1'));
        Session::forget('1');
        //Session::flush();
        var_dump(Session::all());
    }

    public function test(): void
    {
        echo 1;
    }
}

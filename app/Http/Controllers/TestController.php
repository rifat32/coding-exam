<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    public function changePassword()
    {
        $password =   Hash::make("abcdefgh");
        DB::table('users')
            ->where(["id" => 1])
            ->update([
                "password" => $password
            ]);
        return response()->json(["message" => "this route should be secure and hidden.comment out this. Yow are done with changing password."]);
    }
}

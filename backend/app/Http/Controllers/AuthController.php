<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

  public function login(Request $request)
  {
    return response()->json([
      "token" => Auth::attempt($request->only(["email", "password"]))
    ]);
  }

}

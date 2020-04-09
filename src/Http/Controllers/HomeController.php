<?php


namespace Thomas\Bundle\Http\Controllers;


use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function login()
    {
        return view("thomas::home");
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function pageView($routeName, $page = null)
    {
        $viewName = ($page) ? $routeName.'.'.$page : $routeName;
        if (\View::exists($viewName)) {
            return view($viewName);
        } else {
            abort(404);
        }
    }
}
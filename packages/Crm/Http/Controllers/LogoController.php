<?php

namespace packages\Crm\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogoController extends Controller
{
   

    public function index()
    {
        return view('crm::logos.index');
    }

}



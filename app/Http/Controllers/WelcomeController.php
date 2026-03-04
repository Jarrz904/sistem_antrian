<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Mengarahkan ke file resources/views/public/welcome.blade.php
        return view('public.welcome');
    }
}
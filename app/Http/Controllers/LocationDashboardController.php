<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('location-dashboard');
    }

    public function asnUpload()
    {
        return view('location-asn-upload');
    }
} 
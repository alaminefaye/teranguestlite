<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ServicesController extends Controller
{
    public function index(): View
    {
        return view('pages.guest.services.index', [
            'title' => 'Nos Services',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $completedStatuses = ['ready_for_pickup', 'completed', 'picked_up', 'archived'];

        $stats = [
            'birth_total' => BirthCertificate::count(),
            'birth_completed' => BirthCertificate::whereIn('status', $completedStatuses)->count(),
            'death_total' => DeathCertificate::count(),
            'death_completed' => DeathCertificate::whereIn('status', $completedStatuses)->count(),
        ];

        return view('home', compact('stats'));
    }

    public function guidelines()
    {
        return view('information.guidelines');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Skill;
use App\Models\Report;

class AdminsController extends Controller
{
    public function index()
    {
        $userCount = User::count();
        $skillCount = Skill::count();
        $unprocessedReportCount = Report::where('status', 'unprocessed')->count();

        return view('admin.index', compact('userCount', 'skillCount', 'unprocessedReportCount'));
    }
}
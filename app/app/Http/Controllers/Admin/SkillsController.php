<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    public function index(Request $request)
    {
        $query = Skill::with('user'); 

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $skills = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.skills.index', compact('skills'));
    }

    public function edit(Skill $skill)
    {
        return view('admin.skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean', 
        ]);

        $skill->title = $request->input('title');
        $skill->description = $request->input('description');
        $skill->is_active = $request->has('is_active');
        $skill->save();

        return redirect()->route('admin.skills.index')->with('success', 'スキル情報が更新されました。');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return redirect()->route('admin.skills.index')->with('success', 'スキルが削除されました。');
    }
}
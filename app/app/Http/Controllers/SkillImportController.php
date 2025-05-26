<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Skill;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class SkillImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
    'skill_file' => 'required|file|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,application/octet-stream,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
]);


        // 読み込み
        $data = Excel::toArray([], $request->file('skill_file'));
    $rows = $data[0];

    // ヘッダー行スキップ
    if (isset($rows[0]) && strtolower(trim($rows[0][0])) === 'title') {
        array_shift($rows);
    }

    $validated = [];
    $hasError = false;

    foreach ($rows as $index => $row) {
        $title = trim($row[0] ?? '');
        $category = trim($row[1] ?? '');
        $description = trim($row[2] ?? '');

        $rowData = [
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'error' => '',
        ];

        if ($title === '' || $category === '' || $description === '') {
            $rowData['error'] = '必須項目が未入力です';
            $hasError = true;
        }

        $validated[] = $rowData;
    }

    Session::put('import_skills', $validated);
    Session::put('import_has_error', $hasError);

    return redirect('/skill/import/confirm');
    }












    public function confirm()
    {
        $rows = Session::get('import_skills', []);

        return view('user.skill_import_conf', ['skills' => $rows]);
    }

    public function execute()
    {
        $rows = Session::get('import_skills', []);
        $userId = auth()->id();

        foreach ($rows as $row) {
            if (!isset($row[0], $row[1], $row[2])) continue;

            Skill::create([
                'user_id' => $userId,
                'title' => $row[0],
                'category' => $row[1],
                'description' => $row[2],
            ]);
        }

        Session::forget('import_skills');
        return redirect('/skill/manage')->with('message', 'スキルをインポートしました。');
    }
}

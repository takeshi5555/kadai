<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Skill;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\ToCollection;

class SkillsImport implements ToCollection
{
    public function collection($rows)
    {
        // ここはこれまで通り空でOK
    }
}


class SkillImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'skill_file' => 'required|file|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,application/octet-stream,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        try {
            $data = Excel::toCollection(new SkillsImport, $request->file('skill_file'));
            $rows = $data[0];

            if (isset($rows[0][0]) && strtolower(trim($rows[0][0])) === 'title') {
                $rows->shift(); // Collectionのshift()メソッドで最初の要素を削除
            }

            $validated = [];
            $hasError = false;

            foreach ($rows as $index => $row) {
                // $row は数値インデックスの配列としてアクセスします
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

        } catch (\Exception $e) {
            return back()->withErrors(['skill_file' => 'ファイルの読み込み中にエラーが発生しました: ' . $e->getMessage()]);
        }
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

            if (!isset($row['title'], $row['category'], $row['description'])) {
                continue;
            }

            Skill::create([
                'user_id' => $userId,
                'title' => $row['title'],
                'category' => $row['category'],
                'description' => $row['description'],
            ]);
        }

        Session::forget('import_skills');
        return redirect('/skill/manage')->with('message', 'スキルをインポートしました。');
    }
}

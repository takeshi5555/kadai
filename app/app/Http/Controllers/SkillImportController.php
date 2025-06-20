<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skill;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SkillsImport implements ToCollection
{
    public function collection($rows)
    {
        return $rows;
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

            if ($rows->isNotEmpty() && isset($rows[0][0]) && strtolower(trim($rows[0][0])) === 'title') {
                $rows->shift();
            }

            $validated = [];
            $hasError = false;

            // 既存のカテゴリ一覧を取得
            $existingCategories = Skill::select('category')->distinct()->pluck('category')->sort()->values()->toArray();

            // ★ここから「その他」カテゴリの処理を追加★
            $otherCategory = null;
            $filteredCategories = [];
            foreach ($existingCategories as $cat) {
                if ($cat === 'その他') {
                    $otherCategory = $cat;
                } else {
                    $filteredCategories[] = $cat;
                }
            }
            // フィルタリングされたカテゴリをソート（「その他」は含まず）
            sort($filteredCategories); 

            // 「その他」があれば、最後に結合する
            if ($otherCategory !== null) {
                $filteredCategories[] = $otherCategory;
            }
            // ★「その他」カテゴリの処理ここまで★

            foreach ($rows as $index => $row) {
                $title = trim($row[0] ?? '');
                $category = trim($row[1] ?? ''); // CSVからのカテゴリ
                $description = trim($row[2] ?? '');

                $rowData = [
                    'title' => $title,
                    'original_category' => $category, // CSVからのカテゴリをそのまま保持
                    'description' => $description,
                    'error' => '',
                ];

                if ($title === '' || $category === '' || $description === '') {
                    $rowData['error'] = '必須項目が未入力です';
                    $hasError = true;
                } 
                else if (!in_array($category, $filteredCategories)) { // フィルタリング後のリストでチェック
                    $rowData['error'] = '既存カテゴリに一致しません。修正してください。';
                    $hasError = true;
                }

                $validated[] = $rowData;
            }

            Session::put('import_skills', $validated);
            Session::put('import_has_error', $hasError);
            Session::put('existing_categories', $filteredCategories); // 修正後のリストをセッションに保存

            return redirect('/skill/import/confirm');

        } catch (\Exception $e) {
            \Log::error('Skill Import Error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return back()->withErrors(['skill_file' => 'ファイルの読み込み中にエラーが発生しました。ファイル形式と内容を確認してください。']);
        }
    }

    public function confirm()
    {
        $skillsToImport = Session::get('import_skills', []);
        $existingCategories = Session::get('existing_categories', []); // 修正後のリストを使用

        if (empty($skillsToImport)) {
            return redirect('/skill/import')->with('error', 'インポートするファイルが選択されていません。');
        }

        return view('user.skill_import_conf', [
            'skills' => $skillsToImport,
            'existingCategories' => $existingCategories,
        ]);
    }

    // execute メソッドは前回の回答のままで大丈夫です。
    // バリデーションルールで Rule::in($existingCategories) を使っているため、
    // ここで並び順を操作しても問題ありません。
    public function execute(Request $request)
    {
        $userId = Auth::id(); 

        $submittedTitles = $request->input('title', []);
        $submittedCategories = $request->input('category', []);
        $submittedDescriptions = $request->input('description', []);
        
        if (empty($submittedTitles) && empty($submittedCategories) && empty($submittedDescriptions)) {
            Session::forget('import_skills');
            Session::forget('import_has_error');
            Session::forget('existing_categories');
            return redirect('/skill/manage')->with('message', 'インポートするスキルはありませんでした。');
        }

        $existingCategories = Session::get('existing_categories', []); 

        $rules = [];
        foreach ($submittedTitles as $index => $title) {
            $rules["title.{$index}"] = 'required|string|max:255';
            $rules["category.{$index}"] = ['required', 'string', 'max:100', Rule::in($existingCategories)];
            $rules["description.{$index}"] = 'required|string|max:1000'; 
        }

        $validatedData = $request->validate($rules, [
            'title.*.required' => 'スキル名を入力してください。',
            'title.*.max' => 'スキル名は255文字以内で入力してください。',
            'category.*.required' => 'カテゴリを選択してください。',
            'category.*.in' => '選択されたカテゴリは無効です。',
            'description.*.required' => '説明を入力してください。',
            'description.*.max' => '説明は1000文字以内で入力してください。',
        ]);

        $importedCount = 0;
        foreach ($validatedData['title'] as $index => $finalTitle) {
            $finalCategory = $validatedData['category'][$index];
            $finalDescription = $validatedData['description'][$index] ?? null;

            Skill::create([
                'user_id' => $userId, 
                'title' => $finalTitle,
                'category' => $finalCategory,
                'description' => $finalDescription,
            ]);
            $importedCount++;
        }

        Session::forget('import_skills');
        Session::forget('import_has_error');
        Session::forget('existing_categories');
        
        return redirect('/skill/manage')->with('message', "スキルを{$importedCount}件インポートしました。");
    }
}
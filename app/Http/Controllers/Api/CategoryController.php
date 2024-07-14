<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ebook;
use App\Models\LessonPlan;

use App\Models\Ppt;

use App\Models\Video;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use HelpersTrait;

    public function index()
    {
        $categories = Category::get();
        return $this->returnData('data', $categories, "All categories");
    }
    public function show($id)
    {
        $category = Category::find($id);
        return $this->returnData('data', $category, "category");
    }
    public function getAllCategoryData(Request $request, $id)
    {
        // $request->validate([
        //     'unit_id' => 'required|integer',
        // ]);
        $category = Category::findOrFail($id);

        if ($request->filled('unit_id')) {
            $videos = Video::where('category_id', $id)->where('unit_id', $request->unit_id)->get();
            $ebooks = Ebook::where('category_id', $id)->where('unit_id', $request->unit_id)->get();
            $lessonPlans = LessonPlan::where('category_id', $id)->where('unit_id', $request->unit_id)->get();
            $ppts = PPT::where('category_id', $id)->where('unit_id', $request->unit_id)->get();

            $data = [
                'category' => $category,
                'video' => $videos,
                'web' => $ebooks,
                'file' => $lessonPlans->merge($ppts),
            ];
            return $this->returnData('data', $data, "All Data");
        } else {
            // return response()->json(['message' => 'The unit is required'], 404);
            return $this->returnError('S000', "The unit is required");


        }




    }

    public function download($type, $id)
    {
        $model = null;

        switch ($type) {
            case 'video':
                $model = Video::findOrFail($id);
                break;
            case 'ebook':
                $model = Ebook::findOrFail($id);
                break;
            case 'lesson-plan':
                $model = LessonPlan::findOrFail($id);
                break;
            case 'ppt':
                $model = PPT::findOrFail($id);
                break;
            default:
                abort(404);
        }

        if ($model->file_path) {
            return response()->download(storage_path('app/' . $model->file_path));
        }

        return response()->json(['error' => 'File not available'], 404);
    }
}

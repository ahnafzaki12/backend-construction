<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => true,
            'data' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $project = new Project();
        $project->title = $request->title;
        $project->description = $request->description;
        $project->location = $request->location;
        $project->year = $request->year;
        $project->category = $request->category;
        $project->save();

        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage !== null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = time() . $project->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/projects/' . $fileName);

                // Cek apakah file sumber ada
                if (!File::exists($sourcePath)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'File not found at source path: ' . $sourcePath
                    ], 404);
                }

                // Pastikan folder tujuan ada
                if (!File::exists(public_path('uploads/projects'))) {
                    File::makeDirectory(public_path('uploads/projects'), 0755, true);
                }

                // Pindahkan file
                try {
                    File::move($sourcePath, $destPath);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Error moving file: ' . $e->getMessage()
                    ], 500);
                }

                // Simpan nama file ke database
                $project->image = $fileName;
                $project->save();

                // Hapus file sementara dari temp
                $tempImage->delete();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Temporary image not found'
                ], 404);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Project added successfully',
            'data' => $project
        ]);
    }



    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if ($project == null) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $project->title = $request->title;
        $project->description = $request->description;
        $project->location = $request->location;
        $project->year = $request->year;
        $project->category = $request->category;
        $project->save();

        // ✅ Ganti gambar jika ada imageId
        if ($request->imageId > 0) {
            $oldImage = $project->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage !== null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);
                $fileName = time() . $project->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/projects/' . $fileName);

                // Pastikan folder tujuan ada
                if (!File::exists(public_path('uploads/projects'))) {
                    File::makeDirectory(public_path('uploads/projects'), 0755, true);
                }

                // Pindahkan file TANPA resize/crop
                File::move($sourcePath, $destPath);

                // Hapus file lama (jika ada)
                if (!empty($oldImage)) {
                    $oldPath = public_path('uploads/projects/' . $oldImage);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                // Simpan file baru
                $project->image = $fileName;
                $project->save();

                // Bersihkan dari temp
                if (File::exists($sourcePath)) {
                    File::delete($sourcePath);
                }

                $tempImage->delete();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Project updated successfully',
            'data' => $project
        ]);
    }

    public function show($id){
        $project = Project::find($id);

        if ($project == null) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $project
        ]);
    }

    public function destroy($id){
        $project = Project::find($id);

        if ($project == null) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found'
            ]);
        }

        File::delete(public_path('uploads/projects/'.$project->image));

        $project->delete();

        return response()->json([
            'status' => true,
            'message' => 'Project deleted successfully'
        ]);
    }

}

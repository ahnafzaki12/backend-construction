<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => true,
            'data' => $services,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:services,slug'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $model = new Service();
        $model->title = $request->title;
        $model->short_desc = $request->short_desc;
        $model->slug = Str::slug($request->slug);
        $model->content = $request->content;
        $model->status = $request->status;
        $model->save();

        // Save temp image
        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage !== null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = time() . $model->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/services/' . $fileName);

                // Pindahkan file TANPA resize atau edit
                File::move($sourcePath, $destPath);

                // Simpan nama file ke database
                $model->image = $fileName;
                $model->save();
            }
        }


        return response()->json([
            'status' => true,
            'message' => 'Service added successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $service = Service::find($id);

        if ($service == null) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $service,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if ($service == null) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:services,slug,' . $id . ',id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $service->title = $request->title;
        $service->short_desc = $request->short_desc;
        $service->slug = Str::slug($request->slug);
        $service->content = $request->content;
        $service->status = $request->status;
        $service->save();

        // ✅ Ganti gambar jika ada imageId
        if ($request->imageId > 0) {
            $oldImage = $service->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage !== null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);
                $fileName = time() . $service->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/services/' . $fileName);

                // Pastikan folder tujuan ada
                if (!File::exists(public_path('uploads/services'))) {
                    File::makeDirectory(public_path('uploads/services'), 0755, true);
                }

                // Pindahkan file TANPA resize/crop
                File::move($sourcePath, $destPath);

                // Hapus file lama (jika ada)
                if (!empty($oldImage)) {
                    $oldPath = public_path('uploads/services/' . $oldImage);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                // Simpan file baru
                $service->image = $fileName;
                $service->save();

                // Bersihkan dari temp
                if (File::exists($sourcePath)) {
                    File::delete($sourcePath);
                }

                $tempImage->delete();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Service updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if ($service == null) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ]);
        }

        File::delete(public_path('uploads/projects/'.$service->image));

        $service->delete();

        return response()->json([
            'status' => true,
            'message' => 'Service deleted successfully',
        ]);
    }
}

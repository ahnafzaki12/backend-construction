<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\People;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeopleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $people = People::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => true,
            'data' => $people,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $people = new People();
        $people->name = $request->name;
        $people->position = $request->position;
        $people->bio = $request->bio;
        $people->email = $request->email;
        $people->phone = $request->phone;
        $people->linkedin = $request->linkedin;
        $people->save();

        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage !== null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = time() . $people->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/people/' . $fileName);

                // Cek apakah file sumber ada
                File::move($sourcePath, $destPath);

                // Simpan nama file ke database
                $people->image = $fileName;
                $people->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'People added successfully',
            'data' => $people
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $people = People::find($id);

        if ($people == null) {
            return response()->json([
                'status' => false,
                'message' => 'People not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $people
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $people = People::find($id);

        if (!$people) {
            return response()->json([
                'status' => false,
                'message' => 'People not found',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $people->name = $request->name;
        $people->position = $request->position;
        $people->bio = $request->bio;
        $people->email = $request->email;
        $people->phone = $request->phone;
        $people->linkedin = $request->linkedin;
        $people->save();

        if ($request->imageId > 0) {
            $oldimage = $people->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage !== null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = time() . $people->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/people/' . $fileName);

                // Pastikan folder tujuan ada
                if (!File::exists(public_path('uploads/people'))) {
                    File::makeDirectory(public_path('uploads/people'), 0755, true);
                }

                // Pindahkan file dari temp ke folder people
                File::move($sourcePath, $destPath);

                // Hapus file gambar lama jika ada
                if (!empty($oldimage)) {
                    $oldPath = public_path('uploads/people/' . $oldimage);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                // Simpan file baru
                $people->image = $fileName;
                $people->save();

                if (File::exists($sourcePath)) {
                    File::delete($sourcePath);
                }

                $tempImage->delete();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'People updated successfully',
            'data' => $people
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $people = People::find($id);

        if ($people == null) {
            return response()->json([
                'status' => false,
                'message' => 'People not found'
            ]);
        }

        File::delete(public_path('uploads/people/' . $people->image));

        $people->delete();

        return response()->json([
            'status' => true,
            'message' => 'People deleted successfully'
        ]);
    }
}

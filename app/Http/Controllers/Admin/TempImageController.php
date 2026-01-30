<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TempImageController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpg,jpeg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors('image')
            ]);
        }

        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;

        // Simpan data di database
        $model = new TempImage();
        $model->name = $imageName;
        $model->save();

        // Simpan file asli ke uploads/temp (tanpa resize)
        $image->move(public_path('uploads/temp'), $imageName);

        return response()->json([
            'status' => true,
            'data' => $model,
            'message' => "Image uploaded successfully."
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;



class PostController extends Controller
{
    /**
     * Ambil semua post
     */
    public function index()
    {
        $posts = Post::latest()->get();
        return response()->json($posts);
    }

    /**
     * Simpan post baru
     */
   public function store(Request $request)
{
    // Validasi data input
    $validated = $request->validate([
        'title'     => 'required|string|max:255',
        'author'    => 'required|string|max:255',
        'excerpt'   => 'required|string|max:255',
        'category'  => 'required|string|max:255',
        'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',  // Membuat image opsional
        'featured'  => 'nullable|integer',
    ]);

    // Upload image jika ada
    if ($request->hasFile('image')) {
        $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
        $request->file('image')->move(public_path('uploads/posts'), $imageName);
        $validated['image'] = 'uploads/posts/' . $imageName;
    }

    // Membuat post baru dengan data yang sudah tervalidasi
    $post = Post::create($validated);

    // Mengembalikan respons sukses dengan data post
    return response()->json([
        'message' => 'Post berhasil dibuat',
        'data' => $post
    ], 201);
}


    /**
     * Detail post
     */
    public function show($id)
    {
        $post = Post::find($id);

        if ($post == null) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $post
        ]);
    }

    /**
     * Update post
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:255',
            'author'    => 'required|string|max:255',
            'excerpt'   => 'required|string|max:255',
            'category'  => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'featured'  => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Proses upload gambar baru jika ada
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Hapus gambar lama jika ada
            if ($post->image && file_exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/posts'), $imageName);
            $validated['image'] = 'uploads/posts/' . $imageName;
        }

        // Update data post
        $post->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Post berhasil diperbarui',
            'data' => $post
        ]);
    }
    /**
     * Hapus post
     */
    public function destroy($id)
    {
        // Temukan post berdasarkan ID
        $post = Post::find($id);

        // Jika post tidak ditemukan, kembalikan respons
        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found'
            ]);
        }

        // Tentukan path gambar yang akan dihapus
        $imagePath = public_path($post->image); // Menggunakan path yang sudah disimpan di database

        // Periksa apakah file gambar ada, jika ada maka hapus
        if (file_exists($imagePath)) {
            File::delete($imagePath); // Menghapus gambar dari server
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ]);
        }

        // Hapus post dari database
        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully'
        ]);
    }
}

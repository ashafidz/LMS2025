<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Menerima gambar dari editor teks soal (Jodit) dan membalas dengan format uploader bawaan Jodit.
 */
class QuestionImageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:1',
            'files.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ], [
            'files.required' => 'Pilih gambar yang akan diunggah.',
            'files.array' => 'Pilih gambar yang akan diunggah.',
            'files.max' => 'Unggah satu gambar setiap kali.',
            'files.*.required' => 'Pilih gambar yang akan diunggah.',
            'files.*.image' => 'File harus berupa gambar (JPG, PNG, atau GIF).',
            'files.*.mimes' => 'Format gambar harus JPG, PNG, atau GIF.',
            'files.*.max' => 'Ukuran gambar maksimal 2 MB.',
            // PHP drops a file above upload_max_filesize before Laravel sees it
            'files.*.uploaded' => 'Gambar gagal diunggah. Ukuran gambar maksimal 2 MB.',
        ]);

        // HTTP 200 on purpose: Jodit only reads data.messages from a 2xx reply. On 4xx it shows the bare status text
        // ("Unprocessable Content") instead of the reason.
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'time' => now()->toDateTimeString(),
                'data' => [
                    'messages' => $validator->errors()->all(),
                    'code' => 422,
                ],
            ]);
        }

        $path = $request->file('files')[0]->store('question-images/'.Auth::id(), 'public');
        // Root-relative, so the stored HTML does not depend on APP_URL.
        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        return response()->json([
            'success' => true,
            'time' => now()->toDateTimeString(),
            'data' => [
                'baseurl' => '',
                'files' => [$url],
                'isImages' => [true],
                'messages' => [],
                'code' => 220,
            ],
        ]);
    }
}

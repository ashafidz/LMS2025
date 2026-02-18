<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSecuritySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizSecurityController extends Controller
{
    /**
     * Show the security settings form for a quiz
     */
    public function edit(Quiz $quiz)
    {
        // Simple check: hanya instructor yang bisa akses
        if (!Auth::user()->hasRole('instructor')) {
            abort(403, 'Unauthorized');
        }

        // Load security setting (atau buat default jika belum ada)
        $securitySetting = $quiz->securitySetting ?? new QuizSecuritySetting([
            'enable_camera_detection' => false,
            'enable_tab_detection' => false,
            'enable_question_shuffle' => false,
            'camera_violation_threshold' => 3,
            'tab_violation_threshold' => 5,
            'face_detection_interval_seconds' => 5,
        ]);

        // return view('instructor.quiz.security-settings', compact('quiz', 'securitySetting'));
        return view('instructor.quizzes.security-settings', compact('quiz', 'securitySetting'));
    }

    /**
     * Menyimpan atau memperbarui pengaturan keamanan untuk sebuah kuis
     */
    public function update(Request $request, Quiz $quiz)
    {
        // Validasi: Pastikan hanya pembuat kuis (instruktur) yang bisa mengatur keamanan
        if (!Auth::user()->hasRole('instructor')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke halaman ini',
            ], 403);
        }

        // Validasi input data dari formulir di browser
        $validated = $request->validate([
            'enable_camera_detection' => 'boolean', // Status aktif deteksi kamera
            'enable_tab_detection' => 'boolean',    // Status aktif deteksi pindah tab
            'enable_question_shuffle' => 'boolean', // Status aktif pengacakan soal
            'camera_violation_threshold' => 'nullable|integer|min:1|max:20', // Batas maksimal salah kamera
            'tab_violation_threshold' => 'nullable|integer|min:1|max:50',    // Batas maksimal pindah tab
            'face_detection_interval_seconds' => 'nullable|integer|min:3|max:30', // Jeda waktu deteksi AI
        ]);

        // Karena checkbox HTML hanya mengirim data jika dicentang, kita paksa nilainya (true/false)
        $validated['enable_camera_detection'] = $request->has('enable_camera_detection');
        $validated['enable_tab_detection'] = $request->has('enable_tab_detection');
        $validated['enable_question_shuffle'] = $request->has('enable_question_shuffle');

        // Gunakan nilai default jika instruktur mengosongkan angka ambang batas
        $validated['camera_violation_threshold'] = $request->input('camera_violation_threshold', 3);
        $validated['tab_violation_threshold'] = $request->input('tab_violation_threshold', 5);
        $validated['face_detection_interval_seconds'] = $request->input('face_detection_interval_seconds', 5);

        try {
            DB::beginTransaction();

            // Simpan ke tabel 'quiz_security_settings'. 
            // 'updateOrCreate' akan mencari berdasarkan quiz_id, jika ada maka update, jika tidak maka buat baru.
            $quiz->securitySetting()->updateOrCreate(
                ['quiz_id' => $quiz->id],
                $validated
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan keamanan berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            // Batalkan simpan jika terjadi error teknis
            DB::rollBack();
            Log::error('Gagal menyimpan pengaturan keamanan kuis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan pengaturan.',
            ], 500);
        }
    }

    /**
     * Get current security settings (for AJAX)
     */
    public function show(Quiz $quiz)
    {
        if (!Auth::user()->hasRole('instructor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $securitySetting = $quiz->securitySetting;

        if (!$securitySetting) {
            return response()->json([
                'success' => true,
                'data' => [
                    'enable_camera_detection' => false,
                    'enable_tab_detection' => false,
                    'enable_question_shuffle' => false,
                    'camera_violation_threshold' => 3,
                    'tab_violation_threshold' => 5,
                    'face_detection_interval_seconds' => 5,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $securitySetting
        ]);
    }

    /**
     * Delete security settings (reset to default)
     */
    public function destroy(Quiz $quiz)
    {
        if (!Auth::user()->hasRole('instructor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $quiz->securitySetting()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan keamanan berhasil direset ke default.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting quiz security settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mereset pengaturan.',
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LessonAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PointService;
use Carbon\Carbon;

class StudentAssignmentController extends Controller
{
    /**
     * Menerima dan menyimpan file tugas yang dikumpulkan oleh siswa.
     */
    public function submit(Request $request, LessonAssignment $assignment)
    {
        // !! kode komen, tidak terpakai tapi tolong jangan dihapus
        // =============================================================
        // TAMBAHKAN VALIDASI BATAS WAKTU DI SINI (WAJIB) 
        // =============================================================
        // if ($assignment->due_date && Carbon::now()->isAfter($assignment->due_date)) {
        //     return back()->with('error', 'Batas waktu pengumpulan tugas telah berakhir.');
        // }
        // =============================================================


        $user = Auth::user();

        // Validasi: pastikan file ada dan tipenya adalah pdf atau zip
        $validated = $request->validate([
            'submission_file' => 'required|file|mimes:pdf,zip|max:20480', // Maksimal 20MB
        ]);

        // Cek apakah siswa sudah pernah mengumpulkan untuk tugas ini
        $existingSubmission = $assignment->submissions()->where('user_id', $user->id)->first();

        if (!$existingSubmission) {
            PointService::addPoints(
                user: $user,
                course: $assignment->lesson->module->course,
                activity: 'pass_assignment',
                lesson: $assignment->lesson,
                description_meta: $assignment->lesson->title
            );
        }
        if ($existingSubmission) {
            // Jika sudah ada, hapus file lama dari storage sebelum menyimpan yang baru
            \Illuminate\Support\Facades\Storage::disk('public')->delete($existingSubmission->file_path);
        }

        // Cek apakah siswa sudah pernah mengumpulkan untuk tugas ini
        // $existingSubmission = $assignment->submissions()->where('user_id', $user->id)->first();
        // if ($existingSubmission) {
        //     // Jika sudah ada, kita bisa memilih untuk menimpanya atau menolak.
        //     // Untuk sekarang, kita akan menimpanya.
        //     // Anda bisa menambahkan logika untuk menghapus file lama di sini jika perlu.
        // }

        // Simpan file ke storage lokal
        // Format path: assignment_submissions/{assignment_id}/{user_id}/nama_file.pdf
        $path = $validated['submission_file']->store(
            'assignment_submissions/' . $assignment->id . '/' . $user->id,
            'public'
        );

        // Buat atau perbarui catatan pengumpulan di database
        $assignment->submissions()->updateOrCreate(
            ['user_id' => $user->id], // Kunci untuk mencari
            [
                'file_path' => $path,
                'submitted_at' => now(),
                'grade' => null,
                'feedback' => null,
                'status' => 'submitted',
            ]
        );

        return back()->with('success', 'Tugas Anda berhasil dikumpulkan!');
    }
}

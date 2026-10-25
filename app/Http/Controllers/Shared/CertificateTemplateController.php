<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\CertificateTemplateRequest;
use App\Models\CertificateTemplate;
use App\Services\CertificateRenderer;
use App\Services\CertificateTemplateService;
use App\Support\CertificateFonts;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pengelolaan template sertifikat oleh admin dan superadmin (route sama, prefix berbeda).
 */
class CertificateTemplateController extends Controller
{
    public function index(CertificateTemplateService $service)
    {
        $templates = CertificateTemplate::query()
            ->with(['creator:id,name', 'activator:id,name'])
            ->orderByDesc('version')
            ->get();

        return view('shared-admin.certificate-templates.index', [
            'templates' => $templates,
            'activeTemplate' => $service->active(),
            'routePrefix' => $this->prefix(),
        ]);
    }

    public function create()
    {
        return view('shared-admin.certificate-templates.editor', $this->editorData(null));
    }

    public function edit(CertificateTemplate $template)
    {
        return view('shared-admin.certificate-templates.editor', $this->editorData($template));
    }

    public function store(CertificateTemplateRequest $request, CertificateTemplateService $service): RedirectResponse
    {
        $basedOn = $request->filled('based_on_id') ? CertificateTemplate::findOrFail($request->input('based_on_id')) : null;

        try {
            $template = $service->createVersion([
                'name' => $request->input('name'),
                'change_note' => $request->input('change_note'),
                'orientation' => $request->input('orientation'),
                'elements' => $request->elementsForStorage(),
            ], $request->file('background'), $basedOn, $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route($this->prefix().'.certificate-templates.index')
            ->with('success', "Versi {$template->version} tersimpan. Klik Jadikan Aktif bila sudah siap dipakai.");
    }

    /** PDF contoh dari draf di editor, tanpa menyimpan apa pun. */
    public function previewDraft(CertificateTemplateRequest $request, CertificateTemplateService $service, CertificateRenderer $renderer)
    {
        $basedOn = $request->filled('based_on_id') ? CertificateTemplate::findOrFail($request->input('based_on_id')) : null;
        $temporaryPath = null;

        try {
            if ($request->hasFile('background')) {
                [$temporaryPath, $orientation] = $service->storeTemporaryBackground($request->file('background'));
                $path = $temporaryPath;
            } else {
                if (! Storage::disk('public')->exists($basedOn->background_path)) {
                    $message = 'Gambar latar versi ini tidak ditemukan di server. Unggah gambar latar baru.';

                    return response()->json(['message' => $message, 'errors' => ['background' => [$message]]], 422);
                }

                $path = $basedOn->background_path;
                $orientation = $basedOn->orientation;
            }

            $draft = new CertificateTemplate([
                'background_path' => $path,
                'orientation' => $request->input('orientation') ?: $orientation,
                'elements' => $request->elementsForStorage(),
            ]);

            return $renderer->samplePdf($draft)->stream('pratinjau-sertifikat.pdf');
        } catch (DomainException $e) {
            // Contoh: header gambar valid tetapi isinya rusak. Editor menampilkan pesan ini, bukan halaman error.
            return response()->json(['message' => $e->getMessage(), 'errors' => ['background' => [$e->getMessage()]]], 422);
        } catch (\Throwable $e) {
            // Error tak terduga (mis. dompdf): catat di log, kirim JSON singkat ke editor tanpa detail teknis
            report($e);

            return response()->json(['message' => 'Pratinjau gagal dibuat. Coba lagi.'], 500);
        } finally {
            if ($temporaryPath) {
                Storage::disk('local')->delete($temporaryPath);
            }
        }
    }

    public function preview(CertificateTemplate $template, CertificateRenderer $renderer)
    {
        return $renderer->samplePdf($template)->stream('pratinjau-sertifikat-v'.$template->version.'.pdf');
    }

    public function activate(CertificateTemplate $template, CertificateTemplateService $service): RedirectResponse
    {
        $service->activate($template, request()->user());

        return back()->with('success', "Versi {$template->version} ({$template->name}) sekarang aktif. Sertifikat yang diunduh siswa mulai sekarang memakai desain ini.");
    }

    public function deactivateAll(CertificateTemplateService $service): RedirectResponse
    {
        $service->deactivateAll(request()->user());

        return back()->with('success', 'Template dinonaktifkan. Sertifikat kembali memakai desain bawaan.');
    }

    public function destroy(CertificateTemplate $template, CertificateTemplateService $service): RedirectResponse
    {
        try {
            $service->delete($template);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Versi {$template->version} dihapus.");
    }

    private function prefix(): string
    {
        return Str::before((string) Route::currentRouteName(), '.');
    }

    private function editorData(?CertificateTemplate $template): array
    {
        return [
            'template' => $template,
            'routePrefix' => $this->prefix(),
            'fonts' => CertificateFonts::options(),
            'fontFamilies' => collect(CertificateFonts::keys())->mapWithKeys(fn ($key) => [$key => CertificateFonts::cssFamily($key)])->all(),
            'boldFonts' => CertificateFonts::boldKeys(),
            'italicFonts' => CertificateFonts::italicKeys(),
            'elementLabels' => CertificateTemplateRequest::ELEMENT_KEYS,
        ];
    }
}

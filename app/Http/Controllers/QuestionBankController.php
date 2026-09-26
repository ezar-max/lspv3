<?php

namespace App\Http\Controllers;

use App\Models\MasterQuestionBank;
use App\Models\MasterProductSpecification;
use App\Models\SchemeMasterInstrument;
use App\Http\Requests\StoreQuestionBankRequest;
use App\Http\Requests\ImportQuestionBankRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuestionBankController extends Controller
{
    /**
     * Simpan butir pertanyaan baru (PG / Esai / Lisan)
     */
    public function store(StoreQuestionBankRequest $request)
    {
        $validated = $request->validated();

        // Handle gambar soal jika ada
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('bank-soal', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        // Tentukan nomor urut berikutnya jika tidak diisi
        if (empty($validated['order'])) {
            $lastOrder = MasterQuestionBank::where('scheme_master_instrument_id', $validated['scheme_master_instrument_id'])->max('order') ?? 0;
            $validated['order'] = $lastOrder + 1;
        }

        MasterQuestionBank::create($validated);

        return redirect()->back()->with('sukses', 'Butir soal berhasil ditambahkan ke bank soal!');
    }

    /**
     * Update butir pertanyaan yang sudah ada
     */
    public function update(StoreQuestionBankRequest $request, $id)
    {
        $question = MasterQuestionBank::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($question->image_path && file_exists(public_path($question->image_path))) {
                @unlink(public_path($question->image_path));
            }
            $path = $request->file('image')->store('bank-soal', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $question->update($validated);

        return redirect()->back()->with('sukses', 'Butir soal #' . $question->order . ' berhasil diperbarui!');
    }

    /**
     * Hapus butir pertanyaan
     */
    public function destroy($id)
    {
        $question = MasterQuestionBank::findOrFail($id);
        $instrumentId = $question->scheme_master_instrument_id;

        if ($question->image_path && file_exists(public_path($question->image_path))) {
            @unlink(public_path($question->image_path));
        }

        $question->delete();

        return redirect()->back()->with('sukses', 'Butir soal berhasil dihapus dari bank soal.');
    }

    /**
     * Hapus beberapa butir pertanyaan sekaligus (Bulk Delete)
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('question_ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $ids = array_filter(array_map('intval', (array)$ids));

        if (empty($ids)) {
            return redirect()->back(fallback: route('admin.master-muk.index'))->with('error', 'Tidak ada butir soal yang dipilih untuk dihapus.');
        }

        DB::beginTransaction();
        try {
            $questions = MasterQuestionBank::whereIn('id', $ids)->get();
            $count = $questions->count();
            $instrumentId = $questions->first()?->scheme_master_instrument_id;

            foreach ($questions as $question) {
                if ($question->image_path && file_exists(public_path($question->image_path))) {
                    @unlink(public_path($question->image_path));
                }
                $question->delete();
            }

            DB::commit();
            $fallbackUrl = $instrumentId ? route('admin.master-muk.manage', $instrumentId) : route('admin.master-muk.index');
            return redirect()->back(fallback: $fallbackUrl)->with('sukses', "Berhasil menghapus {$count} butir soal sekaligus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back(fallback: route('admin.master-muk.index'))->with('error', 'Gagal menghapus butir soal terpilih: ' . $e->getMessage());
        }
    }

    /**
     * Simpan Spesifikasi Mutu Produk (FR.IA.11)
     */
    public function storeSpec(Request $request)
    {
        $request->validate([
            'scheme_master_instrument_id' => 'required|exists:scheme_master_instruments,id',
            'spec_name' => 'required|string|max:255',
            'standard_tolerance' => 'required|string',
            'order' => 'nullable|integer',
        ]);

        $order = $request->input('order');
        if (empty($order)) {
            $maxOrder = MasterProductSpecification::where('scheme_master_instrument_id', $request->input('scheme_master_instrument_id'))->max('order') ?? 0;
            $order = $maxOrder + 1;
        }

        MasterProductSpecification::create([
            'scheme_master_instrument_id' => $request->input('scheme_master_instrument_id'),
            'spec_name' => $request->input('spec_name'),
            'standard_tolerance' => $request->input('standard_tolerance'),
            'order' => $order,
        ]);

        return redirect()->back()->with('sukses', 'Parameter spesifikasi produk berhasil ditambahkan!');
    }

    /**
     * Update Spesifikasi Mutu Produk (FR.IA.11)
     */
    public function updateSpec(Request $request, $id)
    {
        $request->validate([
            'spec_name' => 'required|string|max:255',
            'standard_tolerance' => 'required|string',
            'order' => 'nullable|integer',
        ]);

        $spec = MasterProductSpecification::findOrFail($id);
        $spec->update([
            'spec_name' => $request->input('spec_name'),
            'standard_tolerance' => $request->input('standard_tolerance'),
            'order' => $request->input('order', $spec->order),
        ]);

        return redirect()->back()->with('sukses', 'Parameter spesifikasi produk berhasil diperbarui!');
    }

    /**
     * Hapus Spesifikasi Mutu Produk (FR.IA.11)
     */
    public function destroySpec($id)
    {
        $spec = MasterProductSpecification::findOrFail($id);
        $spec->delete();

        return redirect()->back()->with('sukses', 'Parameter spesifikasi produk berhasil dihapus.');
    }

    /**
     * Export Bank Soal ke format CSV atau JSON
     */
    public function export($instrumentId, Request $request)
    {
        $instrument = SchemeMasterInstrument::with(['skema', 'unitKompetensi', 'questionBanks', 'productSpecifications'])->findOrFail($instrumentId);
        $format = $request->get('format', 'csv');

        if ($format === 'json') {
            $data = [
                'instrument_code' => $instrument->instrument_code,
                'title' => $instrument->title,
                'skema' => $instrument->skema->nama_skema ?? '',
                'unit' => $instrument->unitKompetensi->kode_unit ?? '',
                'time_limit_minutes' => $instrument->time_limit_minutes,
                'questions' => $instrument->questionBanks->map(function ($q) {
                    return [
                        'order' => $q->order,
                        'question_type' => $q->question_type,
                        'question_text' => $q->question_text,
                        'options' => $q->options,
                        'correct_answer' => $q->correct_answer,
                        'rubric_guide' => $q->rubric_guide,
                        'points' => $q->points,
                    ];
                }),
                'product_specs' => $instrument->productSpecifications->map(function ($s) {
                    return [
                        'spec_name' => $s->spec_name,
                        'standard_tolerance' => $s->standard_tolerance,
                        'order' => $s->order,
                    ];
                }),
            ];

            $fileName = 'bank_soal_' . $instrument->instrument_code . '_' . date('Ymd_His') . '.json';
            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }

        // Default CSV Export
        $fileName = 'bank_soal_' . $instrument->instrument_code . '_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($instrument) {
            $file = fopen('php://output', 'w');
            // Header baris CSV
            fputcsv($file, ['No', 'Tipe_Soal', 'Pertanyaan', 'Opsi_A', 'Opsi_B', 'Opsi_C', 'Opsi_D', 'Kunci_Jawaban', 'Rubrik_Penjelasan', 'Poin']);

            if ($instrument->questionBanks->isEmpty()) {
                // Jika belum ada soal, sediakan contoh baris template untuk memudahkan import
                fputcsv($file, [
                    1,
                    'multiple_choice',
                    'Contoh butir pertanyaan nomor 1: Manakah yang merupakan prosedur K3 utama?',
                    'Menggunakan APD lengkap sebelum memulai pekerjaan',
                    'Langsung menyalakan mesin tanpa pemeriksaan',
                    'Mengabaikan instruksi keselamatan kerja',
                    'Mempercepat waktu kerja tanpa pelindung',
                    'A',
                    'Acuan standar keselamatan kerja bengkel/TUK.',
                    1,
                ]);
                fputcsv($file, [
                    2,
                    'multiple_choice',
                    'Contoh butir pertanyaan nomor 2: Sikap kerja yang wajib diterapkan saat uji kompetensi adalah...',
                    'Cepat namun tidak teliti',
                    'Disiplin, teliti, dan sesuai SOP teknis',
                    'Bekerja tanpa memperhatikan manual book',
                    'Mengubah desain kerja tanpa koordinasi',
                    'B',
                    'Rujukan dimensi kompetensi task management skills.',
                    1,
                ]);
            } else {
                foreach ($instrument->questionBanks as $q) {
                    $options = $q->options ?? [];
                    fputcsv($file, [
                        $q->order,
                        $q->question_type,
                        $q->question_text,
                        $options['A'] ?? '',
                        $options['B'] ?? '',
                        $options['C'] ?? '',
                        $options['D'] ?? '',
                        $q->correct_answer,
                        $q->rubric_guide,
                        $q->points,
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate bank soal otomatis berbasis Unit & KUK skema
     */
    public function generateAuto($id)
    {
        $instrument = SchemeMasterInstrument::with(['skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja'])->findOrFail($id);
        $skema = $instrument->skema;

        if (!$skema) {
            return redirect()->back()->with('error', 'Skema sertifikasi tidak ditemukan.');
        }

        $codeKey = SchemeMasterInstrument::normalizeCode($instrument->instrument_code);
        $units = $skema->unitKompetensi;
        $order = MasterQuestionBank::where('scheme_master_instrument_id', $instrument->id)->max('order') ?? 0;
        $createdCount = 0;

        DB::beginTransaction();
        try {
            if (in_array($codeKey, ['ia_05', 'ia05'])) {
                foreach ($units as $unit) {
                    foreach ($unit->elemenKompetensi as $elemen) {
                        $kukUtama = $elemen->kriteriaUnjukKerja->first();
                        $order++;
                        $createdCount++;

                        MasterQuestionBank::create([
                            'scheme_master_instrument_id' => $instrument->id,
                            'kriteria_unjuk_kerja_id' => $kukUtama?->id,
                            'question_type' => 'multiple_choice',
                            'question_text' => "Pada pelaksanaan unit kompetensi '{$unit->judul_unit}', khususnya pada tahapan {$elemen->nama_elemen}, tindakan yang paling tepat sesuai dengan SOP dan prosedur K3 adalah...",
                            'options' => [
                                'A' => "Melakukan pemeriksaan persiapan kerja, mengenakan APD sesuai standar, dan memastikan instruksi kerja dipahami dengan benar.",
                                'B' => "Langsung memulai pekerjaan tanpa melakukan pengecekan awal peralatan dan lingkungan kerja.",
                                'C' => "Mengabaikan prosedur K3 apabila pekerjaan diperkirakan selesai dalam waktu singkat.",
                                'D' => "Mengubah parameter teknis secara mandiri tanpa konfirmasi kepada penanggung jawab TUK.",
                            ],
                            'correct_answer' => 'A',
                            'rubric_guide' => "Asesi memahami tahapan kerja pada elemen '{$elemen->nama_elemen}' sesuai standar SKKNI unit {$unit->kode_unit}.",
                            'points' => 1,
                            'order' => $order,
                        ]);

                        if ($createdCount >= 25) break 2;
                    }
                }
            } elseif (in_array($codeKey, ['ia_06', 'ia06'])) {
                foreach ($units as $unit) {
                    foreach ($unit->elemenKompetensi as $elemen) {
                        $kukUtama = $elemen->kriteriaUnjukKerja->first();
                        $order++;
                        $createdCount++;

                        MasterQuestionBank::create([
                            'scheme_master_instrument_id' => $instrument->id,
                            'kriteria_unjuk_kerja_id' => $kukUtama?->id,
                            'question_type' => 'essay',
                            'question_text' => "Jelaskan langkah-langkah sistematis dan prinsip kerja yang wajib Anda terapkan saat melakukan '{$elemen->nama_elemen}' pada unit kompetensi {$unit->judul_unit}!",
                            'options' => null,
                            'correct_answer' => "Asesi wajib menjelaskan: 1. Persiapan alat dan bahan sesuai SOP, 2. Tahapan pelaksanaan {$elemen->nama_elemen}, 3. Pemeriksaan mutu hasil kerja dan penerapan K3.",
                            'rubric_guide' => "Memuaskan (M) jika asesi mampu menguraikan minimal 3 poin acuan kerja dengan benar dan runut.",
                            'points' => 1,
                            'order' => $order,
                        ]);

                        if ($createdCount >= 10) break 2;
                    }
                }
            } elseif (in_array($codeKey, ['ia_07', 'ia_03', 'ia07', 'ia03'])) {
                foreach ($units as $unit) {
                    foreach ($unit->elemenKompetensi as $elemen) {
                        $kukUtama = $elemen->kriteriaUnjukKerja->first();
                        $order++;
                        $createdCount++;

                        MasterQuestionBank::create([
                            'scheme_master_instrument_id' => $instrument->id,
                            'kriteria_unjuk_kerja_id' => $kukUtama?->id,
                            'question_type' => 'oral',
                            'question_text' => "Apa yang harus Anda lakukan jika terjadi kendala teknis atau ketidaksesuaian saat proses '{$elemen->nama_elemen}' berlangsung?",
                            'options' => null,
                            'correct_answer' => "Menghentikan proses kerja sementara dengan aman, mengidentifikasi sumber masalah, dan melaporkan sesuai jalur hierarki SOP.",
                            'rubric_guide' => "Klarifikasi lisan aspek kritis K3 dan penanganan masalah teknis.",
                            'points' => 1,
                            'order' => $order,
                        ]);

                        if ($createdCount >= 10) break 2;
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('sukses', "Berhasil men-generate {$createdCount} butir soal otomatis dari KUK Skema.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal men-generate soal: ' . $e->getMessage());
        }
    }

    /**
     * Import Bank Soal dari CSV, Google Form / Google Sheets, atau JSON
     */
    public function import(ImportQuestionBankRequest $request)
    {
        $instrumentId = $request->input('scheme_master_instrument_id');
        $instrument = SchemeMasterInstrument::findOrFail($instrumentId);
        $codeKey = SchemeMasterInstrument::normalizeCode($instrument->instrument_code);
        $titleLower = strtolower($instrument->title ?? '');

        if (str_contains($titleLower, 'esai') || str_contains($titleLower, 'essay') || str_contains($titleLower, 'uraian')) {
            $defaultType = 'essay';
        } elseif (str_contains($titleLower, 'pilihan ganda') || str_contains($titleLower, 'cbt') || str_contains($titleLower, 'pg')) {
            $defaultType = 'multiple_choice';
        } elseif (str_contains($titleLower, 'lisan') || str_contains($titleLower, 'wawancara')) {
            $defaultType = 'oral';
        } else {
            $defaultType = match($codeKey) {
                'ia_06', 'ia06' => 'essay',
                'ia_07', 'ia07', 'ia_03', 'ia03' => 'oral',
                default => 'multiple_choice',
            };
        }

        $rawContent = null;
        $isJson = false;

        // 1. Ambil data dari Google Form atau Google Spreadsheet URL jika diisi
        $inputUrl = trim($request->input('google_form_url', $request->input('google_sheet_url', '')));
        
        if (!empty($inputUrl)) {
            // A. Jika berupa tautan Google Form (forms.gle atau docs.google.com/forms)
            if (str_contains($inputUrl, 'docs.google.com/forms/') || str_contains($inputUrl, 'forms.gle/')) {
                DB::beginTransaction();
                try {
                    $importedCount = $this->importFromGoogleForm($inputUrl, $instrument, $defaultType);
                    if ($importedCount === 0) {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Tidak ditemukan butir pertanyaan yang valid pada Google Form tersebut.');
                    }
                    DB::commit();
                    return redirect()->back()->with('sukses', "Berhasil mengimpor {$importedCount} butir soal langsung dari Google Form!");
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Gagal mengimpor dari Google Form: ' . $e->getMessage());
                }
            }

            // B. Jika berupa tautan Google Spreadsheet
            if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $inputUrl, $matches)) {
                $spreadsheetId = $matches[1];
                $csvExportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";

                // Tangkap tab gid jika ada
                if (preg_match('/[#&?]gid=([0-9]+)/', $inputUrl, $gidMatches)) {
                    $csvExportUrl .= "&gid=" . $gidMatches[1];
                }

                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(15)
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->get($csvExportUrl);

                    if ($response->failed() || str_contains(substr($response->body(), 0, 350), '<!DOCTYPE html')) {
                        return redirect()->back()->with('error', 'Tidak dapat mengakses Google Spreadsheet. Pastikan pengaturan akses spreadsheet disetel ke "Siapa saja yang memiliki link dapat melihat" (Public/Viewer).');
                    }

                    $rawContent = $response->body();
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Gagal mengunduh data dari Google Sheets: ' . $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', 'Format tautan tidak dikenali. Pastikan memasukkan tautan Google Form (contoh: https://forms.gle/... atau https://docs.google.com/forms/...) atau unggah berkas CSV.');
            }
        } 
        // 2. Ambil data dari Berkas yang diunggah
        elseif ($request->hasFile('file_import')) {
            $file = $request->file('file_import');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'json') {
                $isJson = true;
                $rawContent = file_get_contents($file->getRealPath());
            } else {
                $rawContent = file_get_contents($file->getRealPath());
            }
        }

        if (empty($rawContent)) {
            return redirect()->back()->with('error', 'Data impor kosong atau berkas tidak dapat dibaca.');
        }

        DB::beginTransaction();
        try {
            $importedCount = 0;
            $currentMaxOrder = MasterQuestionBank::where('scheme_master_instrument_id', $instrument->id)->max('order') ?? 0;

            if ($isJson) {
                $data = json_decode($rawContent, true);
                if (isset($data['questions']) && is_array($data['questions'])) {
                    foreach ($data['questions'] as $q) {
                        $currentMaxOrder++;
                        MasterQuestionBank::create([
                            'scheme_master_instrument_id' => $instrument->id,
                            'question_type' => $q['question_type'] ?? $defaultType,
                            'question_text' => $q['question_text'] ?? '',
                            'options' => $q['options'] ?? null,
                            'correct_answer' => $q['correct_answer'] ?? '',
                            'rubric_guide' => $q['rubric_guide'] ?? null,
                            'points' => $q['points'] ?? 1,
                            'order' => $q['order'] ?? $currentMaxOrder,
                        ]);
                        $importedCount++;
                    }
                }
            } else {
                // Parse CSV (Dukung Delimiter Koma & Titik-Koma, BOM UTF-8, serta Google Form responses)
                if (str_starts_with($rawContent, "\xEF\xBB\xBF")) {
                    $rawContent = substr($rawContent, 3);
                }

                $firstLine = strtok($rawContent, "\r\n");
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                $stream = fopen('php://memory', 'r+');
                fwrite($stream, $rawContent);
                rewind($stream);

                $rawHeader = fgetcsv($stream, 4096, $delimiter);
                if (!$rawHeader) {
                    return redirect()->back()->with('error', 'Baris judul (header) CSV tidak ditemukan.');
                }

                // Deteksi nama kolom secara cerdas (mendukung format Google Form & Google Sheets)
                $colMap = [
                    'question' => null,
                    'opt_a' => null,
                    'opt_b' => null,
                    'opt_c' => null,
                    'opt_d' => null,
                    'opt_e' => null,
                    'correct' => null,
                    'rubric' => null,
                    'points' => null,
                    'type' => null,
                    'order' => null,
                ];

                foreach ($rawHeader as $idx => $hName) {
                    $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $hName)));

                    if ($colMap['question'] === null && (str_contains($clean, 'pertanyaan') || str_contains($clean, 'soal') || str_contains($clean, 'question') || str_contains($clean, 'studi kasus'))) {
                        $colMap['question'] = $idx;
                    } elseif ($colMap['correct'] === null && (str_contains($clean, 'kunci') || str_contains($clean, 'jawaban benar') || str_contains($clean, 'correct'))) {
                        $colMap['correct'] = $idx;
                    } elseif ($colMap['opt_a'] === null && ($clean === 'a' || str_contains($clean, 'opsi a') || str_contains($clean, 'pilihan a') || str_contains($clean, 'jawaban a') || str_contains($clean, 'option a'))) {
                        $colMap['opt_a'] = $idx;
                    } elseif ($colMap['opt_b'] === null && ($clean === 'b' || str_contains($clean, 'opsi b') || str_contains($clean, 'pilihan b') || str_contains($clean, 'jawaban b') || str_contains($clean, 'option b'))) {
                        $colMap['opt_b'] = $idx;
                    } elseif ($colMap['opt_c'] === null && ($clean === 'c' || str_contains($clean, 'opsi c') || str_contains($clean, 'pilihan c') || str_contains($clean, 'jawaban c') || str_contains($clean, 'option c'))) {
                        $colMap['opt_c'] = $idx;
                    } elseif ($colMap['opt_d'] === null && ($clean === 'd' || str_contains($clean, 'opsi d') || str_contains($clean, 'pilihan d') || str_contains($clean, 'jawaban d') || str_contains($clean, 'option d'))) {
                        $colMap['opt_d'] = $idx;
                    } elseif ($colMap['opt_e'] === null && ($clean === 'e' || str_contains($clean, 'opsi e') || str_contains($clean, 'pilihan e') || str_contains($clean, 'jawaban e') || str_contains($clean, 'option e'))) {
                        $colMap['opt_e'] = $idx;
                    } elseif ($colMap['rubric'] === null && (str_contains($clean, 'pembahasan') || str_contains($clean, 'rubrik') || str_contains($clean, 'penjelasan') || str_contains($clean, 'rujukan'))) {
                        $colMap['rubric'] = $idx;
                    } elseif ($colMap['points'] === null && (str_contains($clean, 'bobot') || str_contains($clean, 'poin') || str_contains($clean, 'nilai') || str_contains($clean, 'score'))) {
                        $colMap['points'] = $idx;
                    } elseif ($colMap['type'] === null && (str_contains($clean, 'tipe') || str_contains($clean, 'jenis') || str_contains($clean, 'type'))) {
                        $colMap['type'] = $idx;
                    } elseif ($colMap['order'] === null && ($clean === 'no' || str_contains($clean, 'nomor') || str_contains($clean, 'urutan') || $clean === 'order')) {
                        $colMap['order'] = $idx;
                    }
                }

                // Fallback jika tidak ada kolom dengan nama spesifik (menggunakan posisi standar)
                $isNamedHeader = ($colMap['question'] !== null);

                while (($row = fgetcsv($stream, 4096, $delimiter)) !== false) {
                    if (empty(array_filter($row))) continue;

                    if ($isNamedHeader) {
                        $questionText = trim($row[$colMap['question']] ?? '');
                        if (empty($questionText)) continue;

                        $rawType = $colMap['type'] !== null ? strtolower(trim($row[$colMap['type']] ?? '')) : '';
                        if (in_array($rawType, ['pg', 'pilihan ganda', 'multiple_choice', 'cbt', 'mc'])) {
                            $type = 'multiple_choice';
                        } elseif (in_array($rawType, ['esai', 'essay', 'uraian', 'studi kasus'])) {
                            $type = 'essay';
                        } elseif (in_array($rawType, ['lisan', 'oral', 'dpl', 'wawancara'])) {
                            $type = 'oral';
                        } else {
                            $type = $defaultType;
                        }

                        $options = null;
                        if ($type === 'multiple_choice') {
                            $options = [
                                'A' => trim($row[$colMap['opt_a']] ?? ''),
                                'B' => trim($row[$colMap['opt_b']] ?? ''),
                                'C' => trim($row[$colMap['opt_c']] ?? ''),
                                'D' => trim($row[$colMap['opt_d']] ?? ''),
                            ];
                            if ($colMap['opt_e'] !== null && !empty(trim($row[$colMap['opt_e']] ?? ''))) {
                                $options['E'] = trim($row[$colMap['opt_e']]);
                            }
                        }

                        // Kunci jawaban
                        $rawAns = $colMap['correct'] !== null ? trim($row[$colMap['correct']] ?? '') : '';
                        $correctAnswer = 'A';
                        if ($type === 'multiple_choice') {
                            if (preg_match('/^(?:opsi\s+)?([A-Ea-e])(?:\.|\)|\s|$)/i', $rawAns, $m)) {
                                $correctAnswer = strtoupper($m[1]);
                            } elseif (!empty($options)) {
                                foreach ($options as $optKey => $optVal) {
                                    if (!empty($optVal) && (strcasecmp($rawAns, $optVal) === 0 || str_contains(strtolower($optVal), strtolower($rawAns)))) {
                                        $correctAnswer = $optKey;
                                        break;
                                    }
                                }
                            }
                        } else {
                            $correctAnswer = $rawAns ?: 'Memuaskan (M) bila sesuai standar SOP dan K3.';
                        }

                        $rubricGuide = $colMap['rubric'] !== null ? trim($row[$colMap['rubric']] ?? '') : null;
                        $points = ($colMap['points'] !== null && is_numeric($row[$colMap['points']] ?? null)) ? (int)$row[$colMap['points']] : 1;
                        
                        $currentMaxOrder++;
                        $order = ($colMap['order'] !== null && is_numeric($row[$colMap['order']] ?? null)) ? (int)$row[$colMap['order']] : $currentMaxOrder;

                    } else {
                        // Posisi standar indeks
                        if (count($row) < 3 || empty(trim($row[2] ?? ''))) continue;

                        $rawType = strtolower(trim($row[1] ?? ''));
                        if (in_array($rawType, ['pg', 'pilihan ganda', 'multiple_choice', 'cbt', 'mc', ''])) {
                            $type = 'multiple_choice';
                        } elseif (in_array($rawType, ['esai', 'essay', 'uraian', 'studi kasus'])) {
                            $type = 'essay';
                        } elseif (in_array($rawType, ['lisan', 'oral', 'dpl', 'wawancara'])) {
                            $type = 'oral';
                        } else {
                            $type = $defaultType;
                        }

                        $questionText = trim($row[2] ?? '');

                        $options = null;
                        if ($type === 'multiple_choice') {
                            $options = [
                                'A' => trim($row[3] ?? ''),
                                'B' => trim($row[4] ?? ''),
                                'C' => trim($row[5] ?? ''),
                                'D' => trim($row[6] ?? ''),
                            ];
                        }

                        $rawAns = strtoupper(trim($row[7] ?? ($row[3] ?? 'A')));
                        $correctAnswer = in_array($rawAns, ['A', 'B', 'C', 'D', 'E']) ? $rawAns : 'A';
                        $rubricGuide = trim($row[8] ?? '');
                        $points = isset($row[9]) && is_numeric($row[9]) ? (int)$row[9] : 1;

                        $currentMaxOrder++;
                        $order = is_numeric($row[0] ?? null) ? (int)$row[0] : $currentMaxOrder;
                    }

                    MasterQuestionBank::create([
                        'scheme_master_instrument_id' => $instrument->id,
                        'question_type' => $type,
                        'question_text' => $questionText,
                        'options' => $options,
                        'correct_answer' => $correctAnswer,
                        'rubric_guide' => $rubricGuide ?: null,
                        'points' => $points,
                        'order' => $order,
                    ]);
                    $importedCount++;
                }
                fclose($stream);
            }

            DB::commit();

            return redirect()->back()->with('sukses', "Berhasil mengimpor {$importedCount} butir soal ke dalam bank soal!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Ekstrak dan impor butir soal langsung dari tautan Google Form (viewform atau forms.gle)
     */
    protected function importFromGoogleForm(string $url, SchemeMasterInstrument $instrument, string $defaultType): int
    {
        $cleanUrl = trim($url);

        // Jika berupa tautan edit, sesuaikan ke viewform
        if (str_contains($cleanUrl, '/edit')) {
            $cleanUrl = preg_replace('/\/edit.*$/', '/viewform', $cleanUrl);
        } elseif (!str_contains($cleanUrl, '/viewform') && str_contains($cleanUrl, 'docs.google.com/forms/d/')) {
            $cleanUrl = rtrim($cleanUrl, '/') . '/viewform';
        }

        $response = \Illuminate\Support\Facades\Http::withOptions(['allow_redirects' => true])
            ->timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            ])
            ->get($cleanUrl);

        if ($response->failed()) {
            throw new \Exception('Gagal mengakses tautan Google Form (Kode Status: ' . $response->status() . '). Pastikan formulir dapat diakses publik.');
        }

        $html = $response->body();

        // Cari script FB_PUBLIC_LOAD_DATA_
        if (!preg_match('/var\s+FB_PUBLIC_LOAD_DATA_\s*=\s*(.*?);\s*<\/script>/s', $html, $matches)) {
            if (!preg_match('/FB_PUBLIC_LOAD_DATA_\s*=\s*(\[.+?\])\s*;/s', $html, $matches)) {
                throw new \Exception('Tidak dapat mengekstrak butir soal dari Google Form ini. Pastikan formulir dapat diakses secara publik dan tidak dibatasi izin login organisasi.');
            }
        }

        $formData = json_decode($matches[1], true);

        if (!$formData || !isset($formData[1][1]) || !is_array($formData[1][1])) {
            throw new \Exception('Format formulir tidak dikenali atau formulir tidak memiliki butir pertanyaan.');
        }

        $items = $formData[1][1];
        $parsedQuestions = [];

        foreach ($items as $item) {
            $questionText = trim($item[1] ?? '');
            $typeCode = $item[3] ?? null;

            // Lewati section header (type 8), gambar murni, atau pertanyaan kosong
            if ($typeCode === 8 || empty($questionText)) {
                continue;
            }

            $rubricGuide = !empty($item[2]) ? trim($item[2]) : null;
            $options = null;
            $correctAnswer = 'A';
            $points = 1;

            $entries = $item[4][0] ?? null;
            $choicesRaw = $entries[1] ?? null;

            // Periksa opsi pilihan ganda
            $hasChoices = is_array($choicesRaw) && count($choicesRaw) > 0;

            if ($hasChoices) {
                $questionType = 'multiple_choice';
                $options = [];
                $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                $keyFound = null;

                foreach ($choicesRaw as $idx => $choice) {
                    if (!isset($letters[$idx])) break;
                    $letter = $letters[$idx];
                    $optText = trim($choice[0] ?? '');

                    // Deteksi penanda kunci jawaban (misal: *Opsi, Opsi [Kunci], Opsi (Benar))
                    if (preg_match('/^\*\s*(.*)$/', $optText, $m)) {
                        $keyFound = $letter;
                        $optText = trim($m[1]);
                    } elseif (preg_match('/^(.*?)\s*[\(\[]kunci(?:\s+jawaban)?[\)\]]$/i', $optText, $m)) {
                        $keyFound = $letter;
                        $optText = trim($m[1]);
                    } elseif (preg_match('/^(.*?)\s*[\(\[]benar[\)\]]$/i', $optText, $m)) {
                        $keyFound = $letter;
                        $optText = trim($m[1]);
                    }

                    $options[$letter] = $optText;
                }

                if ($keyFound) {
                    $correctAnswer = $keyFound;
                }

                // Cek poin quiz jika ada
                if (isset($entries[4][0][1]) && is_numeric($entries[4][0][1])) {
                    $points = max(1, (int)$entries[4][0][1]);
                }
            } else {
                $questionType = 'essay';
                $correctAnswer = $rubricGuide ?? 'Sesuai dengan kriteria unjuk kerja yang diujikan.';
            }

            $parsedQuestions[] = [
                'type' => $questionType,
                'question_text' => $questionText,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'rubric_guide' => $rubricGuide,
                'points' => $points,
            ];
        }

        if (empty($parsedQuestions)) {
            throw new \Exception('Tidak ditemukan butir pertanyaan yang valid pada Google Form tersebut.');
        }

        $mcCount = count(array_filter($parsedQuestions, fn($q) => $q['type'] === 'multiple_choice'));
        $essayCount = count(array_filter($parsedQuestions, fn($q) => $q['type'] === 'essay'));

        // =====================================================================
        // VALIDASI KESESUAIAN TIPE INSTRUMEN
        // =====================================================================
        // 1. Jika instrumen adalah ESAI (FR.IA.06 / essay):
        if ($defaultType === 'essay') {
            if ($mcCount > 0) {
                throw new \Exception("Gagal mengimpor: Instrumen ini adalah instrumen Pertanyaan Esai/Uraian ({$instrument->instrument_code}), namun Google Form yang Anda tautkan berisi soal Pilihan Ganda ({$mcCount} butir memiliki pilihan A, B, C, D). Anda tidak dapat mengimpor soal pilihan ganda ke instrumen esai. Silakan gunakan Google Form dengan tipe soal Jawaban Singkat atau Paragraf.");
            }
        }

        // 2. Jika instrumen adalah PILIHAN GANDA (FR.IA.05 / multiple_choice / CBT):
        if ($defaultType === 'multiple_choice') {
            if ($mcCount === 0 && $essayCount > 0) {
                throw new \Exception("Gagal mengimpor: Instrumen ini adalah instrumen Pilihan Ganda ({$instrument->instrument_code}), namun Google Form yang Anda tautkan berisi soal Esai/Uraian ({$essayCount} butir tanpa opsi pilihan jawaban A, B, C, D). Anda tidak dapat mengimpor soal esai ke instrumen pilihan ganda. Silakan gunakan Google Form dengan tipe soal Pilihan Ganda (Multiple Choice).");
            }
            if ($mcCount === 0) {
                throw new \Exception("Gagal mengimpor: Tidak ditemukan butir soal Pilihan Ganda dengan opsi pilihan jawaban (A, B, C, D) pada Google Form tersebut.");
            }
        }

        // 3. Jika instrumen adalah LISAN (FR.IA.07, FR.IA.03 / oral):
        if ($defaultType === 'oral') {
            if ($mcCount > 0) {
                throw new \Exception("Gagal mengimpor: Instrumen ini adalah instrumen Pertanyaan Lisan/Wawancara ({$instrument->instrument_code}), namun Google Form yang Anda tautkan berisi soal Pilihan Ganda ({$mcCount} butir). Silakan gunakan Google Form dengan tipe soal pertanyaan terbuka.");
            }
        }

        $currentMaxOrder = MasterQuestionBank::where('scheme_master_instrument_id', $instrument->id)->max('order') ?? 0;
        $importedCount = 0;

        foreach ($parsedQuestions as $q) {
            $currentMaxOrder++;
            MasterQuestionBank::create([
                'scheme_master_instrument_id' => $instrument->id,
                'question_type' => $q['type'],
                'question_text' => $q['question_text'],
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'rubric_guide' => $q['rubric_guide'],
                'points' => $q['points'],
                'order' => $currentMaxOrder,
            ]);

            $importedCount++;
        }

        return $importedCount;
    }
}

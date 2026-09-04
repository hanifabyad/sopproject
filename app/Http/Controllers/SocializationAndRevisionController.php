<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSocialization;
use App\Models\RevisionRequest;
use App\Models\SocializationAttendanceParticipant;
use App\Models\SocializationAttendanceSession;
use App\Models\SopQuiz;
use App\Models\SopQuizQuestion;
use App\Models\User;
use App\Services\AttendanceGeneratorService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SocializationAndRevisionController extends Controller
{
    /**
     * Buat Sesi Presensi Kehadiran QR Code Baru (AJAX)
     */
    public function createAttendanceSession(Request $request)
    {
        $request->validate([
            'company'      => 'required|string|in:pkm,cpt,sbs,gvi,lbs',
            'agenda'       => 'required|string|max:255',
            'document_id'  => 'nullable|exists:documents,id',
            'doc_number'   => 'nullable|string|max:100',
            'session_date' => 'required|date',
            'session_time' => 'nullable|string|max:100',
            'location'     => 'nullable|string|max:200',
            'speaker'      => 'nullable|string|max:150',
        ]);

        $token = Str::random(16);
        $user = Auth::user();

        $session = SocializationAttendanceSession::create([
            'token'        => $token,
            'document_id'  => $request->document_id,
            'user_id'      => $user?->id,
            'company'      => $request->company,
            'agenda'       => $request->agenda,
            'doc_number'   => $request->doc_number,
            'session_date' => $request->session_date,
            'session_time' => $request->session_time ?? '09:00 WIB - Selesai',
            'location'     => $request->location ?? 'Ruang Pertemuan / Unit',
            'speaker'      => $request->speaker ?? ($user?->full_name ?? $user?->username ?? '-'),
            'is_active'    => true,
        ]);

        $baseUrl = url('/');
        $parsed = parse_url($baseUrl);
        $host = $parsed['host'] ?? 'localhost';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $scheme = $parsed['scheme'] ?? 'http';

        $localUrl = "{$scheme}://localhost{$port}/presensi/{$token}";
        $lanIp = gethostbyname(gethostname());
        if (!$lanIp || $lanIp === '127.0.0.1') {
            $lanIp = '192.168.110.169';
        }
        $lanUrl = "{$scheme}://{$lanIp}{$port}/presensi/{$token}";

        // Jika running di domain hosting publik (bukan localhost/127.0.0.1), gunakan host asli
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            $presensiUrl = "{$scheme}://{$host}{$port}/presensi/{$token}";
            $localUrl = $presensiUrl;
            $lanUrl = $presensiUrl;
        } else {
            $presensiUrl = $lanUrl;
        }

        $qrSvg = QrCodeService::svg($presensiUrl, 260);

        return response()->json([
            'success'      => true,
            'token'        => $token,
            'presensi_url' => $presensiUrl,
            'local_url'    => $localUrl,
            'lan_url'      => $lanUrl,
            'lan_ip'       => $lanIp,
            'qr_svg'       => $qrSvg,
            'session'      => $session,
        ]);
    }

    /**
     * Polling Data Peserta Hadir Secara Live (AJAX)
     */
    public function getAttendanceSessionLive($token)
    {
        $session = SocializationAttendanceSession::with(['participants' => function($q) {
            $q->latest('id');
        }])->where('token', $token)->firstOrFail();

        $participants = $session->participants;
        $hadirCount = $participants->where('status', 'Hadir')->count();
        $unpassedCount = $participants->where('status', '!=', 'Hadir')->count();
        $passedCount = $participants->where('quiz_status', 'passed')->count();

        return response()->json([
            'success'               => true,
            'count'                 => $hadirCount, // Hanya yang berstatus sah Hadir (telah lulus kuis)
            'total_registered'      => $participants->count(),
            'participants'          => $participants,
            'unpassed_count'        => $unpassedCount,
            'passed_count'          => $passedCount,
            'has_failed_participant'=> ($unpassedCount > 0),
        ]);
    }

    /**
     * Tampilkan Halaman Scan Presensi Publik Peserta & Kuis Operator
     */
    public function showPresensiPage($token)
    {
        $session = SocializationAttendanceSession::with(['document', 'user'])
            ->where('token', $token)
            ->firstOrFail();

        // Cari dokumen terkait sesi sosialisasi ini
        $document = $session->document;
        if (!$document && !empty($session->doc_number)) {
            $document = Document::where('doc_number', $session->doc_number)
                ->orWhere('title', 'like', '%' . $session->agenda . '%')
                ->first();
        }

        $quiz = null;
        $questions = collect();

        if ($document) {
            $quiz = SopQuiz::firstOrCreate(
                ['document_id' => $document->id],
                [
                    'title'         => 'Uji Pemahaman SOP: ' . $document->title,
                    'passing_score' => 60, // KKM 60
                    'is_active'     => true,
                ]
            );

            if ($quiz->passing_score != 60) {
                $quiz->update(['passing_score' => 60]);
            }

            if ($quiz->questions()->count() !== 15 || $quiz->questions()->where('type', 'essay')->exists()) {
                $quiz->questions()->delete();
                app(SopQuizController::class)->analyzeAndGenerateQuestions($quiz, $document);
            }

            $questions = $quiz->questions()->orderBy('sequence')->get();
        }

        $currentUser = Auth::user();

        return view('public.socializations.presensi', compact('session', 'currentUser', 'document', 'quiz', 'questions'));
    }

    /**
     * Simpan Presensi Kehadiran Peserta (Nama & Jabatan)
     */
    public function submitPresensi(Request $request, $token)
    {
        $request->validate([
            'name'       => 'required|string|min:2|max:100',
            'department' => 'required|string|min:2|max:100',
        ]);

        $session = SocializationAttendanceSession::where('token', $token)->firstOrFail();

        $document = $session->document ?: (
            !empty($session->doc_number) 
                ? Document::where('doc_number', $session->doc_number)->first() 
                : Document::where('title', 'like', '%' . $session->agenda . '%')->first()
        );

        $hasQuiz = false;
        if ($document) {
            $hasQuiz = SopQuiz::where('document_id', $document->id)->where('is_active', true)->exists();
        }

        // Jika dokumen memiliki kuis pemahaman SOP, nama baru masuk daftar hadir jika sudah lulus (>= 60)
        $initialStatus = $hasQuiz ? 'Belum Lulus Kuis' : 'Hadir';

        $participant = SocializationAttendanceParticipant::create([
            'session_id'  => $session->id,
            'user_id'     => Auth::id(),
            'name'        => trim($request->name),
            'department'  => trim($request->department),
            'status'      => $initialStatus,
            'attended_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'has_quiz'    => $hasQuiz,
                'message'     => $hasQuiz 
                    ? "Identitas atas nama {$participant->name} telah dicatat. Silakan selesaikan kuis pemahaman SOP (minimal nilai 60) agar nama Anda sah masuk ke lembar daftar hadir resmi."
                    : "Presensi atas nama {$participant->name} berhasil dicatat sebagai HADIR.",
                'participant' => $participant,
            ]);
        }

        return redirect()->back()->with('success', "Presensi atas nama '{$participant->name}' berhasil dicatat.");
    }

    /**
     * Submit Jawaban Kuis Operator dari Halaman Presensi QR (KKM 60)
     */
    public function submitPresensiQuiz(Request $request, $token)
    {
        $session = SocializationAttendanceSession::where('token', $token)->firstOrFail();

        $request->validate([
            'participant_id' => 'required|exists:socialization_attendance_participants,id',
            'answers'        => 'required|array',
        ]);

        $participant = SocializationAttendanceParticipant::where('session_id', $session->id)
            ->where('id', $request->participant_id)
            ->firstOrFail();

        $document = $session->document ?: (
            !empty($session->doc_number) 
                ? Document::where('doc_number', $session->doc_number)->first() 
                : Document::where('title', 'like', '%' . $session->agenda . '%')->first()
        );

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen SOP terkait sesi ini tidak ditemukan.',
            ], 404);
        }

        $quiz = SopQuiz::where('document_id', $document->id)->firstOrFail();
        $questions = $quiz->questions()->orderBy('sequence')->get();
        $submittedAnswers = $request->input('answers', []);

        $totalQuestions = $questions->count();
        $correctCount = 0;
        $evaluations = [];

        foreach ($questions as $q) {
            $userAns = $submittedAnswers[$q->id] ?? null;
            $userAnsNormalized = $userAns ? strtoupper(trim($userAns)) : null;
            $correctAnsNormalized = strtoupper(trim($q->correct_answer));
            $isCorrect = ($userAnsNormalized === $correctAnsNormalized);

            if ($isCorrect) {
                $correctCount++;
            }

            $options = is_array($q->options) ? $q->options : json_decode($q->options, true);
            $evaluations[] = [
                'id'                  => $q->id,
                'sequence'            => $q->sequence,
                'question'            => $q->question,
                'user_answer'         => $userAnsNormalized,
                'user_answer_text'    => $options[$userAnsNormalized] ?? ($userAnsNormalized ?: 'Tidak Dijawab'),
                'correct_answer'      => $correctAnsNormalized,
                'correct_answer_text' => $options[$correctAnsNormalized] ?? '',
                'is_correct'          => $isCorrect,
            ];
        }

        // Hitung skor skala 0 - 100
        $score = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;
        $passingScore = $quiz->passing_score ?: 60; // KKM 60
        $passed = $score >= $passingScore;
        $quizStatus = $passed ? 'passed' : 'failed';
        $attendanceStatus = $passed ? 'Hadir' : 'Belum Lulus Kuis';

        // Simpan hasil ke data peserta presensi: Hanya sah 'Hadir' jika benar-benar lulus (>= 60)
        $updateData = [
            'quiz_score'        => $score,
            'quiz_status'       => $quizStatus,
            'status'            => $attendanceStatus,
            'quiz_answers'      => $submittedAnswers,
            'quiz_attempted_at' => now(),
        ];
        if ($passed) {
            $updateData['attended_at'] = now();
        }
        $participant->update($updateData);

        return response()->json([
            'success'        => true,
            'score'          => $score,
            'passing_score'  => $passingScore,
            'passed'         => $passed,
            'correct_count'  => $correctCount,
            'total_count'    => $totalQuestions,
            'status'         => $quizStatus,
            'attendance_status' => $attendanceStatus,
            'evaluations'    => $evaluations,
            'participant'    => $participant,
            'message'        => $passed
                ? "Selamat! Anda LULUS dengan nilai {$score}/100 ({$correctCount} dari {$totalQuestions} benar - Standar KKM {$passingScore}). Nama Anda telah resmi dimasukkan ke daftar hadir."
                : "Nilai Anda {$score}/100 ({$correctCount} dari {$totalQuestions} benar - Di bawah KKM {$passingScore}). Sesuai ketentuan, nama Anda BELUM dimasukkan ke daftar hadir. Silakan ulangi kuis sampai benar-benar lulus!",
        ]);
    }

    /**
     * Unduh PDF BS Form 9 Terisi Berdasarkan Data Sesi Presensi QR
     */
    public function downloadSessionPdf($token)
    {
        $session = SocializationAttendanceSession::where('token', $token)->firstOrFail();

        // Hanya peserta yang telah sah LULUS (status 'Hadir') yang masuk ke lembar daftar hadir
        $participantsData = $session->participants()
            ->where('status', 'Hadir')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($p) {
                return [
                    'name'        => $p->name,
                    'department'  => $p->department,
                    'status'      => $p->status,
                    'attended_at' => $p->attended_at,
                ];
            })->toArray();

        $service = new AttendanceGeneratorService();
        $pdfPath = $service->generate([
            'company'      => $session->company,
            'agenda'       => $session->agenda,
            'doc_number'   => $session->doc_number,
            'date'         => $session->session_date ? $session->session_date->translatedFormat('d F Y') : date('d F Y'),
            'time'         => $session->session_time,
            'location'     => $session->location,
            'speaker'      => $session->speaker,
            'participants' => $participantsData,
            'token'        => $session->token,
            'qr_url'       => $session->getPresensiUrl(),
            'min_rows'     => 18,
        ]);

        $cleanAgenda = preg_replace('/[^A-Za-z0-9_-]/', '_', $session->agenda);
        $filename = "Daftar_Hadir_Presensi_{$cleanAgenda}.pdf";

        return response()->download($pdfPath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Halaman Khusus Unggah Bukti Sosialisasi untuk Dokumen SOP Tertentu (Direct dari Email Notifikasi)
     */
    public function createSocialization($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Validasi akses departemen non-admin
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            $isAssigned = \App\Models\DocumentApproval::where('document_id', $document->id)->where('user_id', $user->id)->exists();

            if (!empty($depts) && !in_array($document->department, $depts, true) && !$isAssigned) {
                return redirect()->route('user.socializations.index')
                    ->with('error', 'Anda tidak memiliki wewenang untuk dokumen SOP departemen ini.');
            }
        }

        $existingSocialization = DocumentSocialization::where('document_id', $document->id)
            ->with('user')
            ->latest('id')
            ->first();

        $allUsers = User::orderBy('username', 'asc')->get();

        return view('user.socializations.create', compact('document', 'existingSocialization', 'allUsers'));
    }

    /**
     * Generator Lembar Daftar Hadir Sosialisasi SOP Otomatis (BS Form 9)
     */
    public function generateAttendanceSheet(Request $request)
    {
        $request->validate([
            'company'      => 'required|string|in:pkm,cpt,sbs,gvi,lbs',
            'agenda'       => 'required|string|max:255',
            'doc_number'   => 'nullable|string|max:100',
            'date'         => 'required|string|max:50',
            'time'         => 'nullable|string|max:100',
            'location'     => 'nullable|string|max:200',
            'speaker'      => 'nullable|string|max:150',
            'user_ids'     => 'nullable|array',
            'user_ids.*'   => 'integer|exists:users,id',
            'custom_names' => 'nullable|array',
            'custom_depts' => 'nullable|array',
        ]);

        $participants = [];

        // 1. Tambahkan user akun terdaftar yang dipilih
        if (!empty($request->user_ids)) {
            $selectedUsers = User::whereIn('id', $request->user_ids)->get();
            foreach ($selectedUsers as $u) {
                $name = !empty($u->full_name) ? $u->full_name : $u->username;
                $dept = !empty($u->role) ? $u->role : 'Staff';
                $participants[] = [
                    'name' => $name,
                    'dept' => $dept,
                ];
            }
        }

        // 2. Tambahkan peserta tambahan (tanpa akun / lapangan)
        if (!empty($request->custom_names)) {
            foreach ($request->custom_names as $k => $cName) {
                $cName = trim((string)$cName);
                if (!empty($cName)) {
                    $cDept = trim((string)($request->custom_depts[$k] ?? ''));
                    $participants[] = [
                        'name' => $cName,
                        'dept' => !empty($cDept) ? $cDept : '-',
                    ];
                }
            }
        }

        $service = new \App\Services\AttendanceGeneratorService();
        $pdfPath = $service->generate([
            'company'      => $request->company,
            'agenda'       => $request->agenda,
            'doc_number'   => $request->doc_number,
            'date'         => $request->date,
            'time'         => $request->time ?? '09:00 WIB - Selesai',
            'location'     => $request->location ?? 'Ruang Rapat / Lokasi Unit',
            'speaker'      => $request->speaker ?? (Auth::user()?->full_name ?? Auth::user()?->username),
            'participants' => $participants,
            'min_rows'     => 18,
        ]);

        $filename = 'Daftar_Hadir_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $request->agenda) . '.pdf';

        return response()->download($pdfPath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * 1. Simpan Bukti Sosialisasi SOP (Poin 3)
     */
    public function storeSocialization(Request $request, $id)
    {
        $method = $request->input('attendance_method', 'form');

        $rules = [
            'socialization_date'       => 'required|date',
            'notes'                    => 'nullable|string|max:1000',
            'photos'                   => 'nullable|array|max:10',
            'photos.*'                 => 'file|mimes:jpg,jpeg,png|max:10240',
            'attendance_session_token' => 'nullable|string|max:100',
        ];

        if ($method === 'upload') {
            $rules['attendance_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } else {
            $rules['company'] = 'required|string|in:pkm,cpt,sbs,gvi,lbs';
        }

        $request->validate($rules);

        $document = Document::findOrFail($id);
        $user = Auth::user();

        // 1. Tentukan berkas daftar hadir (Upload Berkas Fisik atau Buat Otomatis Form BS Form 9)
        if ($method === 'upload' && $request->hasFile('attendance_file')) {
            $attendancePath = $request->file('attendance_file')->store('socializations/attendance', 'public');
        } else {
            // Generate otomatis format BS Form 9
            $participants = [];

            // A. Ambil peserta dari Sesi Presensi QR jika sesi telah dibuat/digunakan
            $sessionToken = $request->input('attendance_session_token');
            $sessionQrUrl = null;
            if (!empty($sessionToken)) {
                $session = SocializationAttendanceSession::where('token', $sessionToken)->first();
                if ($session) {
                    $sessionQrUrl = $session->getPresensiUrl();
                    // Hanya ambil peserta yang sah Hadir (lulus kuis atau tanpa kuis)
                    $hadirParticipants = $session->participants()->where('status', 'Hadir')->orderBy('id', 'asc')->get();
                    if ($hadirParticipants->isNotEmpty()) {
                        foreach ($hadirParticipants as $p) {
                            $participants[] = [
                                'name' => $p->name,
                                'dept' => $p->department ?? '-',
                            ];
                        }
                    }
                }
            }

            // B. Tambahkan user akun terdaftar yang dipilih
            if (!empty($request->user_ids)) {
                $selectedUsers = User::whereIn('id', $request->user_ids)->get();
                foreach ($selectedUsers as $u) {
                    $participants[] = [
                        'name' => !empty($u->full_name) ? $u->full_name : $u->username,
                        'dept' => !empty($u->role) ? $u->role : 'Staff',
                    ];
                }
            }

            // C. Tambahkan peserta manual tambahan
            if (!empty($request->custom_names)) {
                foreach ($request->custom_names as $k => $cName) {
                    $cName = trim((string)$cName);
                    if (!empty($cName)) {
                        $cDept = trim((string)($request->custom_depts[$k] ?? ''));
                        $participants[] = [
                            'name' => $cName,
                            'dept' => !empty($cDept) ? $cDept : '-',
                        ];
                    }
                }
            }

            $service = new \App\Services\AttendanceGeneratorService();
            $genAbsPath = $service->generate([
                'company'      => $request->company ?? ($document->company_header ?? 'pkm'),
                'agenda'       => $request->agenda ?? ('Sosialisasi ' . $document->title),
                'doc_number'   => $document->doc_number,
                'date'         => $request->socialization_date ? \Carbon\Carbon::parse($request->socialization_date)->translatedFormat('d F Y') : date('d F Y'),
                'time'         => $request->time ?? '09:00 WIB - Selesai',
                'location'     => $request->location ?? 'Ruang Rapat / Lokasi Unit',
                'speaker'      => $request->speaker ?? ($user->full_name ?? $user->username),
                'participants' => $participants,
                'token'        => $sessionToken,
                'qr_url'       => $sessionQrUrl,
                'min_rows'     => 18,
            ]);

            $fileName = 'daftar_hadir_auto_' . time() . '_' . uniqid() . '.pdf';
            $relPath = 'socializations/attendance/' . $fileName;
            $destPath = storage_path('app/public/' . $relPath);
            if (!is_dir(dirname($destPath))) {
                mkdir(dirname($destPath), 0755, true);
            }
            copy($genAbsPath, $destPath);
            $attendancePath = $relPath;
        }

        // 2. Upload file foto-foto dokumentasi
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('socializations/photos', 'public');
            }
        }

        // 3. Simpan record sosialisasi (Auto Approve karena daftar hadir telah dilampirkan secara sah)
        $socialization = DocumentSocialization::create([
            'document_id'        => $document->id,
            'user_id'            => $user->id,
            'socialization_date' => $request->socialization_date,
            'notes'              => $request->notes,
            'attendance_file'    => $attendancePath,
            'photos'             => $photoPaths,
            'status'             => 'verified',
        ]);

        $document->update([
            'socialization_status' => 'verified',
        ]);

        // 4. Catat ke Audit Trail (Document Logs)
        $document->logs()->create([
            'user_id' => $user->id,
            'action'  => 'socialization_verified',
            'notes'   => 'Bukti sosialisasi SOP (Lembar Daftar Hadir & ' . count($photoPaths) . ' Foto Dokumentasi) berhasil diunggah dan otomatis disahkan (Auto-Approved).',
        ]);

        // 5. Kirim Notifikasi Email ke Admin QMS
        try {
            $adminUsers = User::where('role', 'admin')->whereNotNull('email')->get();
            foreach ($adminUsers as $admin) {
                if (!empty(trim($admin->email))) {
                    $actionUrl = URL::temporarySignedRoute(
                        'login.magic',
                        now()->addDays(7),
                        [
                            'user_id'     => $admin->id,
                            'redirect_to' => route('admin.user_reviews.index', ['tab' => 'socialization']),
                        ]
                    );

                    Mail::to($admin->email)->queue(
                        new \App\Mail\SocializationSubmittedMail($document, $socialization, $user, $actionUrl)
                    );
                }
            }
        } catch (\Throwable $e) {
            \Log::error("Gagal mengirim email notifikasi sosialisasi: " . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bukti sosialisasi SOP beserta lembar daftar hadir berhasil diunggah dan otomatis disahkan.'
            ]);
        }

        return redirect()->back()->with('success', 'Bukti sosialisasi SOP beserta lembar daftar hadir berhasil diunggah dan otomatis disahkan (Verified).');
    }

    /**
     * Ambil data detail sosialisasi dokumen (JSON untuk modal)
     */
    public function showSocialization($id)
    {
        $socialization = null;
        $document = Document::find($id);

        if ($document) {
            $socialization = DocumentSocialization::where('document_id', $document->id)
                ->with('user')
                ->latest()
                ->first();
        } else {
            $socialization = DocumentSocialization::with(['document', 'user'])->find($id);
            if ($socialization) {
                $document = $socialization->document;
            }
        }

        if (!$socialization && !$document) {
            return response()->json([
                'success' => false,
                'message' => 'Data sosialisasi tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success'       => true,
            'socialization' => [
                'id'                 => $socialization ? $socialization->id : 0,
                'document_id'        => $document ? $document->id : ($socialization->document_id ?? 0),
                'doc_title'          => $document->title ?? ($socialization->document->title ?? 'SOP'),
                'doc_number'         => $document->doc_number ?? ($socialization->document->doc_number ?? 'No. Belum Diatur'),
                'department'         => $document->department ?? ($socialization->document->department ?? '-'),
                'revision_number'    => $document->doc_revision ?? ($socialization->document->doc_revision ?? '0'),
                'status'             => $socialization ? ($socialization->status ?? 'submitted') : 'submitted',
                'socialization_date' => ($socialization && $socialization->socialization_date) ? $socialization->socialization_date->format('d F Y') : '-',
                'notes'              => $socialization ? ($socialization->notes ?? '') : '',
                'attendance_file'    => ($socialization && $socialization->attendance_file) ? asset('storage/' . $socialization->attendance_file) : null,
                'photos'             => ($socialization && is_array($socialization->photos)) ? collect($socialization->photos)->map(fn($p) => asset('storage/' . $p))->toArray() : [],
                'user_name'          => ($socialization && $socialization->user) ? ($socialization->user->full_name ?? $socialization->user->username) : 'PIC Unit',
                'user_role'          => ($socialization && $socialization->user) ? ($socialization->user->role ?? 'Staff / PIC') : 'Staff / PIC',
                'created_at'         => ($socialization && $socialization->created_at) ? $socialization->created_at->format('d M Y, H:i') : '-',
                'verify_url'         => $socialization ? route('admin.socializations.verify', $socialization->id) : '#',
                'reject_url'         => $socialization ? route('admin.socializations.reject', $socialization->id) : '#',
            ]
        ]);
    }

    /**
     * Ambil detail bukti sosialisasi langsung berdasarkan ID bukti untuk Admin.
     * Dipisahkan dari endpoint user yang menggunakan document_id agar tidak ambigu.
     */
    public function showAdminSocialization($id)
    {
        $socialization = DocumentSocialization::with(['document', 'user'])->find($id);

        if (!$socialization) {
            return response()->json([
                'success' => false,
                'message' => 'Data sosialisasi tidak ditemukan.'
            ], 404);
        }

        return $this->socializationDetailResponse($socialization);
    }

    private function socializationDetailResponse(DocumentSocialization $socialization)
    {
        $document = $socialization->document;

        return response()->json([
            'success' => true,
            'socialization' => [
                'id' => $socialization->id,
                'document_id' => $document?->id ?? $socialization->document_id,
                'doc_title' => $document?->title ?? 'SOP',
                'doc_number' => $document?->doc_number ?? 'No. Belum Diatur',
                'department' => $document?->department ?? '-',
                'revision_number' => $document?->doc_revision ?? '0',
                'status' => $socialization->status ?? 'submitted',
                'socialization_date' => $socialization->socialization_date ? $socialization->socialization_date->format('d F Y') : '-',
                'notes' => $socialization->notes ?? '',
                'attendance_file' => $socialization->attendance_file ? asset('storage/' . $socialization->attendance_file) : null,
                'photos' => is_array($socialization->photos) ? collect($socialization->photos)->map(fn($p) => asset('storage/' . $p))->toArray() : [],
                'user_name' => $socialization->user?->full_name ?? $socialization->user?->username ?? 'PIC Unit',
                'user_role' => $socialization->user?->role ?? 'Staff / PIC',
                'created_at' => $socialization->created_at?->format('d M Y, H:i') ?? '-',
                'verify_url' => route('admin.socializations.verify', $socialization->id),
                'reject_url' => route('admin.socializations.reject', $socialization->id),
            ]
        ]);
    }

    /**
     * Verifikasi & Pengesahan Bukti Sosialisasi oleh Admin QMS
     */
    public function verifySocialization($id)
    {
        $soc = DocumentSocialization::with('document')->findOrFail($id);
        $soc->update(['status' => 'verified']);

        if ($soc->document) {
            $soc->document->update(['socialization_status' => 'verified']);
            
            \App\Models\DocumentLog::create([
                'document_id' => $soc->document->id,
                'user_id'     => auth()->id(),
                'action'      => 'socialization_verified',
                'notes'       => 'Bukti pelaksanaan sosialisasi SOP telah diverifikasi dan disahkan oleh Admin QMS (' . (auth()->user()->username ?? 'Admin') . ').'
            ]);
        }

        return back()->with('success', "Bukti sosialisasi untuk dokumen [{$soc->document->doc_number}] {$soc->document->title} berhasil diverifikasi dan disahkan.");
    }

    /**
     * Tolak / Minta Perbaikan Bukti Sosialisasi oleh Admin QMS
     */
    public function rejectSocialization(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000'
        ]);

        $soc = DocumentSocialization::with('document')->findOrFail($id);
        $soc->update([
            'status' => 'rejected',
            'notes'  => ($soc->notes ? $soc->notes . "\n\n[Catatan Revisi Admin]: " : "[Catatan Revisi Admin]: ") . $request->admin_notes
        ]);

        if ($soc->document) {
            $soc->document->update(['socialization_status' => 'rejected']);
            
            \App\Models\DocumentLog::create([
                'document_id' => $soc->document->id,
                'user_id'     => auth()->id(),
                'action'      => 'socialization_rejected',
                'notes'       => 'Bukti pelaksanaan sosialisasi ditolak oleh Admin QMS. Alasan: ' . $request->admin_notes
            ]);
        }

        return back()->with('success', "Permintaan revisi bukti sosialisasi telah dikirimkan kepada PIC unit terkait.");
    }

    /**
     * Helper pemetaan departemen berdasarkan role user
     */
    private function getDepartmentsForRole(?string $role): array
    {
        if (!$role) return [];

        switch ($role) {
            case 'KA.DEPT.HC': return ['HC'];
            case 'KA.DEPT.IT': return ['IT'];
            case 'KA.DEPT.QMS':
            case 'Management Representative': return ['QMS'];
            case 'KA.DEPT.HSE': return ['HSE'];
            case 'KA.DEPT.ADMIN & LEGAL': return ['LEGAL', 'ADMIN & LEGAL'];
            case 'KA.DEPT.INTERNAL AUDIT':
            case 'Dept. Internal Audit':
            case 'KA.DEPT.INTERNAL AUDIT & RISK MANAGEMENT': return ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'];
            case 'KA.DEPT.F & A':
            case 'KA.DEPT.KEUANGAN': return ['FINANCE', 'KEUANGAN', 'F & A'];
            case 'KA.DEPT.SALES & MARKETING': return ['LOGISTIC', 'OPS'];
            case 'Ka. BU SPBU': return ['SPBU'];
            case 'Ka. BU Gas & SPBE': return ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'];
            case 'Ka. BU Inmarr': return ['INMAR (CNGM)'];
            case 'Ka. BU CPT':
            case 'Direktur CPT': return ['CPT & MHM', 'SBS', 'GVI'];
            case 'KA.DEPT.PROCRUTMEN': return ['PROCUREMENT'];
            case 'KA.DEPT.CORPORATE SEKTARIS': return ['WAREHOUSE', 'ASET', 'GA'];
            case 'Chief of Staff': return ['WAREHOUSE', 'ASET', 'GA'];
            case 'Chief F&A':
            case 'Ka. Div F&A': return ['FINANCE', 'KEUANGAN & ACCOUNTING'];
            case 'Ka. Div Retail':
            case 'Wa. Ka. Div Retail': return ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'LOGISTIC', 'OPS'];
            default: return [];
        }
    }

    /**
     * 2. User Mengajukan Request Revisi ke Admin (Poin 9)
     */
    public function storeRevisionRequest(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Validasi: User non-admin hanya dapat mengajukan revisi sesuai bidang/departemennya
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            $isAssigned = \App\Models\DocumentApproval::where('document_id', $document->id)->where('user_id', $user->id)->exists();

            if (!in_array($document->department, $depts, true) && !$isAssigned) {
                return redirect()->back()->with('error', 'Anda hanya dapat mengajukan permohonan revisi untuk dokumen SOP di bidang atau departemen Anda.');
            }
        }

        // Cek apakah sudah ada request pending
        $existing = RevisionRequest::where('document_id', $document->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Permohonan revisi sebelumnya masih menunggu persetujuan Admin.');
        }

        $revReq = RevisionRequest::create([
            'document_id' => $document->id,
            'user_id'     => $user->id,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        $document->logs()->create([
            'user_id' => $user->id,
            'action'  => 'revision_requested',
            'notes'   => 'User (' . ($user->full_name ?? $user->username) . ') mengajukan permohonan revisi SOP. Alasan: ' . $request->reason,
        ]);

        // Kirim Notifikasi Email ke Admin QMS dengan Magic Login
        try {
            $adminUsers = User::where('role', 'admin')->whereNotNull('email')->get();
            foreach ($adminUsers as $admin) {
                if (!empty(trim($admin->email))) {
                    $actionUrl = URL::temporarySignedRoute(
                        'login.magic',
                        now()->addDays(7),
                        [
                            'user_id'     => $admin->id,
                            'redirect_to' => route('admin.user_reviews.index', ['tab' => 'revision', 'status' => 'pending']),
                        ]
                    );

                    Mail::to($admin->email)->queue(
                        new \App\Mail\RevisionRequestSubmittedMail($document, $revReq, $user, $actionUrl)
                    );
                }
            }
        } catch (\Throwable $e) {
            \Log::error("Gagal mengirim email permohonan revisi ke Admin: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Permohonan revisi SOP berhasil dikirim ke Admin QMS.');
    }

    /**
     * 3. Admin Menyetujui Request Revisi (Pemberian Jendela 7 Hari) (Poin 9)
     */
    public function approveRevisionRequest(Request $request, $id)
    {
        $revReq = RevisionRequest::with(['document', 'user'])->findOrFail($id);
        $document = $revReq->document;

        DB::transaction(function () use ($revReq, $document, $request) {
            $deadline = now()->addDays(7); // Batas maksimal 7 hari

            $revReq->update([
                'status'      => 'approved',
                'admin_id'    => Auth::id(),
                'approved_at' => now(),
                'deadline_at' => $deadline,
                'admin_notes' => $request->admin_notes,
            ]);

            $document->update([
                'status'            => 'need_revision',
                'revision_deadline' => $deadline,
            ]);

            $document->logs()->create([
                'user_id' => Auth::id(),
                'action'  => 'revision_request_approved',
                'notes'   => 'Admin menyetujui permohonan revisi SOP dari pemohon (' . ($revReq->user->full_name ?? 'User') . ').',
            ]);
        });

        // Kirim email notifikasi ke pemohon (User) dengan signed route
        if ($revReq->user && !empty(trim($revReq->user->email))) {
            try {
                // Tentukan route upload revisi (BU vs Support)
                $isSupport = in_array(strtoupper($document->department), ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL']);
                $routeName = $isSupport ? 'admin.support.creator_revise' : 'admin.BU.creator_revise';

                $uploadUrl = URL::temporarySignedRoute(
                    'login.magic',
                    now()->addDays(30),
                    [
                        'user_id'     => $revReq->user->id,
                        'document_id' => $document->id,
                    ]
                );

                Mail::to($revReq->user->email)->queue(
                    new \App\Mail\RevisionRequestApprovedMail($document, $revReq, $revReq->user, $uploadUrl)
                );
            } catch (\Throwable $e) {
                \Log::error("Gagal mengirim email persetujuan request revisi: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Permohonan revisi disetujui dan notifikasi telah dikirim ke pemohon.');
    }

    /**
     * Admin Menolak Request Revisi
     */
    public function rejectRevisionRequest(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $revReq = RevisionRequest::with('document')->findOrFail($id);
        $document = $revReq->document;

        $revReq->update([
            'status'      => 'rejected',
            'admin_id'    => Auth::id(),
            'admin_notes' => $request->admin_notes,
        ]);

        $document->logs()->create([
            'user_id' => Auth::id(),
            'action'  => 'revision_request_rejected',
            'notes'   => 'Admin menolak permohonan revisi SOP. Alasan penolakan: ' . $request->admin_notes,
        ]);

        return redirect()->back()->with('info', 'Permohonan revisi SOP telah ditolak.');
    }

    /**
     * Tampilkan halaman permohonan revisi untuk User di Sidebar.
     */
    public function userRevisionRequestsIndex(Request $request)
    {
        $user = Auth::user();
        
        // Ambil semua dokumen aktif yang sesuai bidang/departemen user (atau seluruh dokumen jika admin)
        $availableDocumentsQuery = Document::where('status', 'active');
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            $availableDocumentsQuery->where(function($q) use ($user, $depts) {
                if (!empty($depts)) {
                    $q->whereIn('department', $depts);
                }
                $q->orWhereHas('approvals', function($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
        }
        $availableDocuments = $availableDocumentsQuery->orderBy('title', 'asc')->get();

        // Ambil riwayat pengajuan revisi oleh user ini (atau semua jika admin)
        $query = RevisionRequest::with(['document', 'user', 'admin']);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('document', function($dq) use ($search) {
                      $dq->where('title', 'like', "%{$search}%")
                         ->orWhere('doc_number', 'like', "%{$search}%")
                         ->orWhere('department', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        // Stats
        $baseQuery = RevisionRequest::query();
        if ($user->role !== 'admin') {
            $baseQuery->where('user_id', $user->id);
        }
        $totalMyRequests = $baseQuery->count();
        $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();

        return view('user.revision_requests.index', compact(
            'availableDocuments',
            'requests',
            'totalMyRequests',
            'approvedCount',
            'pendingCount',
            'search'
        ));
    }

    /**
     * Tampilkan halaman pusat Bukti Sosialisasi SOP untuk User di Sidebar.
     */
    public function userSocializationsIndex(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil dokumen aktif yang sesuai lingkup bidang/departemen user
        $availableDocumentsQuery = Document::where('status', 'active');
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            $availableDocumentsQuery->where(function($q) use ($user, $depts) {
                if (!empty($depts)) {
                    $q->whereIn('department', $depts);
                }
                $q->orWhereHas('approvals', function($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
        }
        $availableDocuments = $availableDocumentsQuery->orderBy('title', 'asc')->get();

        // 2. Query riwayat sosialisasi yang diunggah
        $query = DocumentSocialization::with(['document', 'user']);
        if ($user->role !== 'admin') {
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('document', function($dq) use ($user) {
                      $depts = $this->getDepartmentsForRole($user->role);
                      if (!empty($depts)) {
                          $dq->whereIn('department', $depts);
                      }
                      $dq->orWhereHas('approvals', function($aq) use ($user) {
                          $aq->where('user_id', $user->id);
                      });
                  });
            });
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('document', function($dq) use ($search) {
                      $dq->where('title', 'like', "%{$search}%")
                         ->orWhere('doc_number', 'like', "%{$search}%")
                         ->orWhere('department', 'like', "%{$search}%");
                  });
            });
        }

        $socializations = $query->latest('id')->paginate(10)->withQueryString();

        // 3. Stats
        $totalUploaded = (clone $query)->count();
        $verifiedCount = (clone $query)->where('status', 'verified')->count();
        $pendingCount = (clone $query)->where('status', 'submitted')->count();
        $needsSocializationCount = $availableDocuments->whereNull('socialization_status')->count();

        $allUsers = User::orderBy('username', 'asc')->get();

        return view('user.socializations.index', compact(
            'availableDocuments',
            'socializations',
            'allUsers',
            'totalUploaded',
            'verifiedCount',
            'pendingCount',
            'needsSocializationCount',
            'search'
        ));
    }
}

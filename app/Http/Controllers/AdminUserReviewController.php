<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSocialization;
use App\Models\SopQuizAttempt;
use App\Models\RevisionRequest;
use App\Models\NewSopRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminUserReviewController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'revision'); // revision, socialization, new_sop, quiz
        $search = $request->input('search');
        $status = $request->input('status');

        // 1. Revision Requests Query
        $revQuery = RevisionRequest::with(['document', 'user', 'admin'])->latest('id');
        if (!empty($status) && $activeTab === 'revision') {
            $revQuery->where('status', $status);
        }
        if (!empty($search) && $activeTab === 'revision') {
            $revQuery->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('document', function ($dq) use ($search) {
                      $dq->where('title', 'like', "%{$search}%")
                         ->orWhere('doc_number', 'like', "%{$search}%")
                         ->orWhere('department', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }
        $revisionRequests = $revQuery->paginate(15, ['*'], 'rev_page')->withQueryString();

        // 2. Socializations Query
        $socQuery = DocumentSocialization::with(['document', 'user'])->latest('id');
        if (!empty($search) && $activeTab === 'socialization') {
            $socQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('document', function ($dq) use ($search) {
                      $dq->where('title', 'like', "%{$search}%")
                         ->orWhere('doc_number', 'like', "%{$search}%")
                         ->orWhere('department', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }
        $socializations = $socQuery->paginate(15, ['*'], 'soc_page')->withQueryString();

        // 3. New SOP Requests Query
        $newSopQuery = NewSopRequest::with(['user', 'admin'])->latest('id');
        if (!empty($status) && $activeTab === 'new_sop') {
            $newSopQuery->where('status', $status);
        }
        if (!empty($search) && $activeTab === 'new_sop') {
            $newSopQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }
        $newSopRequests = $newSopQuery->paginate(15, ['*'], 'sop_page')->withQueryString();

        // 4. SOP Quiz Summary Query (1 SOP per Baris & Status Kelulusan Keseluruhan)
        $quizCategory = $request->input('quiz_category', 'all'); // 'all', 'periodic', 'socialization'

        $sopQuizDocsQuery = Document::where(function ($q) {
            $q->whereHas('quiz')
              ->orWhereHas('socializationSessions')
              ->orWhereHas('quizAttempts');
        })->with([
            'quiz',
            'quizAttempts.user',
            'socializationSessions.participants'
        ])->latest('id');

        if (!empty($search) && $activeTab === 'quiz') {
            $sopQuizDocsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('doc_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $allSopQuizDocs = $sopQuizDocsQuery->get()->map(function ($doc) use ($quizCategory) {
            $socializationParticipants = collect();
            $periodicParticipants = collect();

            // 1. Peserta dari Sesi Sosialisasi Presensi QR (Operator / Lapangan)
            foreach ($doc->socializationSessions as $session) {
                foreach ($session->participants as $p) {
                    if ($p->quiz_score !== null) {
                        $socializationParticipants->push([
                            'source'       => 'Sosialisasi QR / Presensi',
                            'category'     => 'socialization',
                            'name'         => $p->name,
                            'department'   => $p->department ?: ($p->position ?: '-'),
                            'score'        => (int) $p->quiz_score,
                            'status'       => $p->quiz_status,
                            'attempted_at' => $p->quiz_attempted_at ? \Carbon\Carbon::parse($p->quiz_attempted_at) : $p->created_at,
                        ]);
                    }
                }
            }

            // 2. Peserta dari Ujian Akun Internal e-QMS (Evaluasi Berkala 6 Bulan)
            foreach ($doc->quizAttempts as $attempt) {
                $periodicParticipants->push([
                    'source'       => 'Kuis Berkala (Internal e-QMS)',
                    'category'     => 'periodic',
                    'name'         => $attempt->user?->full_name ?: ($attempt->user?->username ?: 'Pengguna'),
                    'department'   => $attempt->user?->role ?: ($attempt->user?->department ?: '-'),
                    'score'        => (int) $attempt->score,
                    'status'       => $attempt->status,
                    'attempted_at' => $attempt->attempt_date ?: $attempt->created_at,
                ]);
            }

            // Tentukan peserta sesuai filter kategori yang dipilih
            if ($quizCategory === 'periodic') {
                $participants = $periodicParticipants;
            } elseif ($quizCategory === 'socialization') {
                $participants = $socializationParticipants;
            } else {
                $participants = $socializationParticipants->concat($periodicParticipants);
            }

            $totalTakers = $participants->count();
            $passedCount = $participants->where('status', 'passed')->count();
            $failedCount = $participants->where('status', 'failed')->count();
            $avgScore    = $totalTakers > 0 ? round($participants->avg('score')) : 0;

            if ($totalTakers === 0) {
                $overallStatus = 'no_participants';
            } elseif ($failedCount > 0) {
                $overallStatus = 'remedial';
            } else {
                $overallStatus = 'all_passed';
            }

            $doc->quiz_participants           = $participants;
            $doc->all_quiz_participants       = $socializationParticipants->concat($periodicParticipants);
            $doc->socialization_participants  = $socializationParticipants;
            $doc->periodic_participants       = $periodicParticipants;
            $doc->socialization_count         = $socializationParticipants->count();
            $doc->periodic_count              = $periodicParticipants->count();
            $doc->quiz_total_takers           = $totalTakers;
            $doc->quiz_passed_count           = $passedCount;
            $doc->quiz_failed_count           = $failedCount;
            $doc->quiz_avg_score              = $avgScore;
            $doc->quiz_overall_status         = $overallStatus;

            return $doc;
        });

        // Filter status kelulusan SOP
        if (!empty($status) && $activeTab === 'quiz') {
            if ($status === 'passed' || $status === 'all_passed') {
                $allSopQuizDocs = $allSopQuizDocs->where('quiz_overall_status', 'all_passed');
            } elseif ($status === 'failed' || $status === 'remedial') {
                $allSopQuizDocs = $allSopQuizDocs->where('quiz_overall_status', 'remedial');
            } elseif ($status === 'no_participants') {
                $allSopQuizDocs = $allSopQuizDocs->where('quiz_overall_status', 'no_participants');
            }
        }

        $totalQuizCount = Document::whereHas('quiz')
            ->orWhereHas('socializationSessions')
            ->orWhereHas('quizAttempts')
            ->count();

        // Statistik peserta kuis per kategori sistem
        $totalPeriodicTakersCount = SopQuizAttempt::count();
        $totalSocializationTakersCount = \App\Models\SocializationAttendanceParticipant::whereNotNull('quiz_score')->count();
        $totalAllQuizTakersCount = $totalPeriodicTakersCount + $totalSocializationTakersCount;

        // Paginate dokumen SOP kuis
        $quizPage = (int) $request->input('quiz_page', 1);
        $perPage = 15;
        $quizSops = new LengthAwarePaginator(
            $allSopQuizDocs->forPage($quizPage, $perPage)->values(),
            $allSopQuizDocs->count(),
            $perPage,
            $quizPage,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'quiz_page']
        );

        $quizAttempts = $quizSops; // fallback alias

        // Counts for Badges & Stats
        $pendingRevisionCount = RevisionRequest::where('status', 'pending')->count();
        $totalSocializationCount = DocumentSocialization::count();
        $pendingSocializationCount = DocumentSocialization::where('status', 'submitted')->count();
        $verifiedSocializationCount = DocumentSocialization::where('status', 'verified')->count();
        $pendingNewSopCount = NewSopRequest::where('status', 'pending')->count();
        $totalNewSopCount = NewSopRequest::count();

        $availableDocuments = Document::where('status', 'active')->orderBy('title', 'asc')->get();

        return view('admin.user_reviews.index', compact(
            'activeTab',
            'search',
            'status',
            'quizCategory',
            'totalPeriodicTakersCount',
            'totalSocializationTakersCount',
            'totalAllQuizTakersCount',
            'revisionRequests',
            'socializations',
            'newSopRequests',
            'quizAttempts',
            'quizSops',
            'pendingRevisionCount',
            'totalSocializationCount',
            'pendingSocializationCount',
            'verifiedSocializationCount',
            'pendingNewSopCount',
            'totalNewSopCount',
            'totalQuizCount',
            'availableDocuments'
        ));
    }
}

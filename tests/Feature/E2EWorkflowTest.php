<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\Library;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class E2EWorkflowTest extends TestCase
{
    private $filesToClean = [];

    private function createMockPdf(string $text, int $pages = 1): UploadedFile
    {
        $pdf = new \setasign\Fpdi\Fpdi();
        for ($i = 0; $i < $pages; $i++) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, $text . " - Page " . ($i + 1));
        }
        $tempFile = tempnam(sys_get_temp_dir(), 'mock_pdf_') . '.pdf';
        $pdf->Output('F', $tempFile);
        $this->filesToClean[] = $tempFile;
        return new UploadedFile(
            $tempFile,
            $text . '.pdf',
            'application/pdf',
            null,
            true
        );
    }

    public function test_full_e2e_approval_workflow()
    {
        Mail::fake();
        DB::beginTransaction();

        try {
            $creator = User::findOrFail(28); // Imam M
            $reviewer1 = User::findOrFail(7); // Trinwetty
            $reviewer2 = User::findOrFail(26); // Yayu Indah Maya
            $reviewer3 = User::findOrFail(15); // Putri Larasati Ariandini
            $finalApprover = User::findOrFail(17); // Zikri
            $admin = User::where('role', 'admin')->firstOrFail();

            $cover = $this->createMockPdf('Cover', 1);
            $isi = $this->createMockPdf('Isi Prosedur', 2);
            $lamp1 = $this->createMockPdf('Lampiran 1', 1);

            $payload = [
                'title'           => 'Test Prosedur',
                'file_cover'      => $cover,
                'file_isi'        => $isi,
                'file_lampiran'   => [$lamp1],
                'company_header'  => 'pkm',
                'doc_number'      => 'SOP-PKM-TEST-001',
                'doc_revision'    => '0',
                'doc_date'        => now()->format('Y-m-d'),
                'creator_id'      => $creator->id,
                'reviewers'       => [$reviewer1->id, $reviewer2->id, $reviewer3->id],
                'final_id'        => $finalApprover->id,
            ];

            $response = $this->actingAs($admin)
                ->post(route('admin.BU.store', ['unit' => 'SPBU']), $payload);

            $response->assertRedirect();
            
            $document = Document::where('title', 'Test Prosedur')->firstOrFail();
            $this->assertEquals('waiting', $document->status);
            $this->assertEquals($creator->id, $document->reviewer_id);

            if ($document->file_cover) $this->filesToClean[] = storage_path('app/public/' . $document->file_cover);
            if ($document->file_lp) $this->filesToClean[] = storage_path('app/public/' . $document->file_lp);
            if ($document->file_isi) $this->filesToClean[] = storage_path('app/public/' . $document->file_isi);
            if ($document->file_lampiran) $this->filesToClean[] = storage_path('app/public/' . $document->file_lampiran);
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $approvals = $document->approvals;
            $this->assertCount(5, $approvals);

            $this->assertEquals('current', $approvals[0]->status);
            $this->assertEquals('pending', $approvals[1]->status);

            $response = $this->actingAs($creator)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'SOP siap di-review.'
                ]);

            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $response = $this->actingAs($reviewer1)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Reviewer 1 approve.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $response = $this->actingAs($reviewer2)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Reviewer 2 approve.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $response = $this->actingAs($reviewer3)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Reviewer 3 approve.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $response = $this->actingAs($finalApprover)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Final approval ok.'
                ]);
            $response->assertRedirect();

            $document->refresh();
            if ($document->file_final) $this->filesToClean[] = storage_path('app/public/' . $document->file_final);
            $this->assertEquals('active', $document->status);

            $library = Library::where('title', 'Test Prosedur')->firstOrFail();
            $this->assertEquals($document->file_final, $library->file_path);

            $finalPdfPath = storage_path('app/public/' . $document->file_final);
            $this->assertFileExists($finalPdfPath);

            $artifactDir = storage_path('app/e2e-artifacts');
            if (!is_dir($artifactDir)) {
                mkdir($artifactDir, 0755, true);
            }
            $artifactPdf = $artifactDir . '/Test-Prosedur-E2E-FINAL.pdf';
            copy($finalPdfPath, $artifactPdf);
            $this->assertFileExists($artifactPdf);

            $layoutFixPdf = $artifactDir . '/Test-Prosedur-E2E-LAYOUT-FIX.pdf';
            copy($finalPdfPath, $layoutFixPdf);
            $this->assertFileExists($layoutFixPdf);

            Mail::assertSent(\App\Mail\NewDocumentReviewMail::class);
            Mail::assertSent(\App\Mail\DocumentApprovedMail::class);

        } finally {
            DB::rollBack();
            foreach (array_unique($this->filesToClean) as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }
    }

    public function test_full_revision_workflow_cycle()
    {
        Mail::fake();
        DB::beginTransaction();

        try {
            $creator = User::findOrFail(28); // Imam M
            $reviewer1 = User::findOrFail(7); // Trinwetty
            $reviewer2 = User::findOrFail(26); // Yayu Indah Maya
            $reviewer3 = User::findOrFail(15); // Putri Larasati Ariandini
            $finalApprover = User::findOrFail(17); // Zikri
            $admin = User::where('role', 'admin')->firstOrFail();

            $cover = $this->createMockPdf('Cover Original', 1);
            $isi = $this->createMockPdf('Isi Original', 2);
            $lamp1 = $this->createMockPdf('Lampiran Original', 1);

            // 1. Create Document
            $payload = [
                'title'           => 'Test Prosedur Revisi',
                'file_cover'      => $cover,
                'file_isi'        => $isi,
                'file_lampiran'   => [$lamp1],
                'company_header'  => 'pkm',
                'doc_number'      => 'SOP-PKM-REV-001',
                'doc_revision'    => '0',
                'doc_date'        => now()->format('Y-m-d'),
                'creator_id'      => $creator->id,
                'reviewers'       => [$reviewer1->id, $reviewer2->id, $reviewer3->id],
                'final_id'        => $finalApprover->id,
            ];

            $response = $this->actingAs($admin)
                ->post(route('admin.BU.store', ['unit' => 'SPBU']), $payload);

            $response->assertRedirect();
            $document = Document::where('title', 'Test Prosedur Revisi')->firstOrFail();

            if ($document->file_cover) $this->filesToClean[] = storage_path('app/public/' . $document->file_cover);
            if ($document->file_lp) $this->filesToClean[] = storage_path('app/public/' . $document->file_lp);
            if ($document->file_isi) $this->filesToClean[] = storage_path('app/public/' . $document->file_isi);
            if ($document->file_lampiran) $this->filesToClean[] = storage_path('app/public/' . $document->file_lampiran);
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            // 2. Creator Approves
            $response = $this->actingAs($creator)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Siap review.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            // 3. Reviewer 1 (Trinwetty) Requests Revision (Reject)
            $response = $this->actingAs($reviewer1)
                ->post(route('reviewer.reject', ['id' => $document->id]), [
                    'notes' => 'Tolong perbaiki bagian Isi Prosedur.'
                ]);
            $response->assertRedirect();

            $document->refresh();
            // Verify status is need_revision
            $this->assertEquals('need_revision', $document->status);

            // Verify email sent to creator
            Mail::assertSent(\App\Mail\DocumentRevisionRequestedMail::class, function ($mail) use ($creator, $reviewer1) {
                return $mail->hasTo($creator->email) &&
                       $mail->requester->id === $reviewer1->id &&
                       strpos($mail->notes, 'Tolong perbaiki') !== false;
            });

            // Verify other reviewers did NOT receive resubmitted mail yet
            Mail::assertNotSent(\App\Mail\DocumentRevisionResubmittedMail::class);

            // 4. Creator/Admin submits revised document
            $revisedIsi = $this->createMockPdf('Isi Revised', 2);
            $response = $this->actingAs($admin)
                ->put(route('admin.BU.update_revision', ['id' => $document->id]), [
                    'title' => 'Test Prosedur Revisi',
                    'file_isi' => $revisedIsi,
                ]);

            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            // Verify status is back to waiting
            $this->assertEquals('waiting', $document->status);

            // Verify reviewer 1 is active (current) again
            $reviewer1Approval = DocumentApproval::where('document_id', $document->id)
                ->where('user_id', $reviewer1->id)
                ->first();
            $this->assertEquals('current', $reviewer1Approval->status);

            // Verify resubmitted email sent to Reviewer 1
            Mail::assertSent(\App\Mail\DocumentRevisionResubmittedMail::class, function ($mail) use ($reviewer1) {
                return $mail->hasTo($reviewer1->email);
            });

            // 5. Reviewers approve again
            $response = $this->actingAs($reviewer1)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Sudah direvisi dengan baik.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $response = $this->actingAs($reviewer2)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Reviewer 2 ok.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            $response = $this->actingAs($reviewer3)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Reviewer 3 ok.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            // 6. Final Approval
            $response = $this->actingAs($finalApprover)
                ->post(route('reviewer.approve', ['id' => $document->id]), [
                    'notes' => 'Final approval approved.'
                ]);
            $response->assertRedirect();
            $document->refresh();
            if ($document->file_final) $this->filesToClean[] = storage_path('app/public/' . $document->file_final);

            $this->assertEquals('active', $document->status);
            $this->assertFileExists(storage_path('app/public/' . $document->file_final));

        } finally {
            DB::rollBack();
            foreach (array_unique($this->filesToClean) as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentApproval;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class QAWorkflowAuditTest extends TestCase
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

    protected function tearDown(): void
    {
        foreach ($this->filesToClean as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    /**
     * Test direct URL access protection (IDOR / Authorization Bypass)
     */
    public function test_reviewer_unauthorized_document_access_is_blocked()
    {
        DB::beginTransaction();
        try {
            $creator = User::findOrFail(28); // Imam M
            $reviewer1 = User::findOrFail(7); // Trinwetty
            $reviewer2 = User::findOrFail(26); // Yayu
            $reviewer3 = User::findOrFail(15); // Putri
            $finalApprover = User::findOrFail(17); // Zikri
            $admin = User::where('role', 'admin')->firstOrFail();

            // Create a reviewer user who is NOT part of the document approvals
            $unauthorizedReviewer = User::create([
                'username'  => 'stranger_danger',
                'full_name' => 'Stranger Danger',
                'email'     => 'stranger@pkmgroup.co.id',
                'password'  => bcrypt('password123'),
                'role'      => 'reviewer',
                'status'    => 1,
            ]);

            $cover = $this->createMockPdf('Confidential Cover', 1);
            $isi = $this->createMockPdf('Confidential Content', 2);

            $payload = [
                'title'           => 'Confidential Audit SOP',
                'file_cover'      => $cover,
                'file_isi'        => $isi,
                'company_header'  => 'pkm',
                'doc_number'      => 'SOP-PKM-CONF-999',
                'doc_revision'    => '0',
                'doc_date'        => now()->format('Y-m-d'),
                'creator_id'      => $creator->id,
                'reviewers'       => [$reviewer1->id, $reviewer2->id, $reviewer3->id],
                'final_id'        => $finalApprover->id,
            ];

            $this->actingAs($admin)
                ->post(route('admin.BU.store', ['unit' => 'SPBU']), $payload);

            $document = Document::where('title', 'Confidential Audit SOP')->latest()->firstOrFail();

            if ($document->file_cover) $this->filesToClean[] = storage_path('app/public/' . $document->file_cover);
            if ($document->file_lp) $this->filesToClean[] = storage_path('app/public/' . $document->file_lp);
            if ($document->file_isi) $this->filesToClean[] = storage_path('app/public/' . $document->file_isi);
            if ($document->file_preview) $this->filesToClean[] = storage_path('app/public/' . $document->file_preview);

            // Assert that the stranger is forbidden from showing/viewing the document details page
            $response = $this->actingAs($unauthorizedReviewer)
                ->get(route('reviewer.show', ['id' => $document->id]));
            $response->assertStatus(403);

            // Assert that the stranger is forbidden from streaming the file
            $response = $this->actingAs($unauthorizedReviewer)
                ->get(route('reviewer.stream.file', ['id' => $document->id]));
            $response->assertStatus(403);

            // Assert that an authorized reviewer CAN access
            $response = $this->actingAs($reviewer1)
                ->get(route('reviewer.show', ['id' => $document->id]));
            $response->assertStatus(200);

            $response = $this->actingAs($reviewer1)
                ->get(route('reviewer.stream.file', ['id' => $document->id]));
            $response->assertStatus(200);

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test duplicate reviewer validation rule check
     */
    public function test_document_creation_fails_when_reviewers_array_has_duplicates()
    {
        DB::beginTransaction();
        try {
            $creator = User::findOrFail(28);
            $reviewer1 = User::findOrFail(7);
            $finalApprover = User::findOrFail(17);
            $admin = User::where('role', 'admin')->firstOrFail();

            $cover = $this->createMockPdf('Cover Test', 1);
            $isi = $this->createMockPdf('Isi Test', 2);

            // Passing duplicate reviewers in array: [7, 7, 17] (fails distinct validation)
            $payload = [
                'title'           => 'Duplicate Reviewer SOP',
                'file_cover'      => $cover,
                'file_isi'        => $isi,
                'company_header'  => 'pkm',
                'doc_number'      => 'SOP-PKM-DUP-111',
                'doc_revision'    => '0',
                'doc_date'        => now()->format('Y-m-d'),
                'creator_id'      => $creator->id,
                'reviewers'       => [$reviewer1->id, $reviewer1->id, $reviewer1->id], // Duplicated!
                'final_id'        => $finalApprover->id,
            ];

            $response = $this->actingAs($admin)
                ->post(route('admin.BU.store', ['unit' => 'SPBU']), $payload);

            // Session has validation errors for reviewers
            $response->assertSessionHasErrors(['msg']);
            $this->assertStringContainsString('reviewers.0', session('errors')->first('msg'));

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test signer validation check when full_name is empty
     */
    public function test_document_creation_fails_when_signer_full_name_is_empty()
    {
        DB::beginTransaction();
        try {
            $creator = User::findOrFail(28);
            $reviewer1 = User::findOrFail(7);
            $reviewer2 = User::findOrFail(26);
            $reviewer3 = User::findOrFail(15);
            $finalApprover = User::findOrFail(17);
            $admin = User::where('role', 'admin')->firstOrFail();

            // Create a user with empty full_name
            $invalidReviewer = User::create([
                'username'  => 'empty_name_user',
                'full_name' => '', // Empty!
                'email'     => 'empty_name@pkmgroup.co.id',
                'password'  => bcrypt('password123'),
                'role'      => 'reviewer',
                'status'    => 1,
            ]);

            $cover = $this->createMockPdf('Cover Empty Name', 1);
            $isi = $this->createMockPdf('Isi Empty Name', 2);

            $payload = [
                'title'           => 'Empty Name SOP',
                'file_cover'      => $cover,
                'file_isi'        => $isi,
                'company_header'  => 'pkm',
                'doc_number'      => 'SOP-PKM-EMP-222',
                'doc_revision'    => '0',
                'doc_date'        => now()->format('Y-m-d'),
                'creator_id'      => $creator->id,
                'reviewers'       => [$reviewer1->id, $invalidReviewer->id, $reviewer3->id],
                'final_id'        => $finalApprover->id,
            ];

            $response = $this->actingAs($admin)
                ->post(route('admin.BU.store', ['unit' => 'SPBU']), $payload);

            // It should redirect back with flash message warning about the unconfigured full name
            $response->assertSessionHasErrors(['msg']);
            $this->assertStringContainsString('belum dikonfigurasi', session('errors')->first('msg'));

        } finally {
            DB::rollBack();
        }
    }
}

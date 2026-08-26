<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\DocumentApproval;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ScratchHcScenarioTest extends TestCase
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

    public function test_run_custom_hc_workflow_scenario()
    {
        // Fake mail to prevent sending real emails during test run
        Mail::fake();

        // No DB transaction, we want this to persist in the local MySQL database!
        // Clean up old SOP-HC-2026 data to allow fresh run
        \App\Models\DocumentApproval::whereHas('document', function($q) {
            $q->where('doc_number', 'SOP-HC-2026');
        })->delete();
        Document::where('doc_number', 'SOP-HC-2026')->delete();

        $creator = User::findOrFail(28); // Imam M
        $reviewer1 = User::findOrFail(7); // Trinwetty
        $reviewer2 = User::findOrFail(26); // Yayu Indah Maya
        $reviewer3 = User::findOrFail(15); // Putri Larasati Ariandini
        $finalApprover = User::findOrFail(17); // Zikri
        $admin = User::where('role', 'admin')->firstOrFail();

        echo "\n1. Menyiapkan berkas PDF untuk SOP-HC-2026...\n";
        $cover = $this->createMockPdf('SOP HC BARU Cover', 1);
        $isi = $this->createMockPdf('SOP HC BARU Isi', 2);
        $lamp1 = $this->createMockPdf('SOP HC BARU Lampiran', 1);

        echo "2. Membuat dokumen baru 'SOP-HC-2026' via Admin...\n";
        $payload = [
            'title'           => 'SOP HC BARU',
            'file_cover'      => $cover,
            'file_isi'        => $isi,
            'file_lampiran'   => [$lamp1],
            'company_header'  => 'pkm',
            'doc_number'      => 'SOP-HC-2026',
            'doc_revision'    => '0',
            'doc_date'        => '2026-08-19',
            'creator_id'      => $creator->id,
            'reviewers'       => [$reviewer1->id, $reviewer2->id, $reviewer3->id],
            'final_id'        => $finalApprover->id,
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.BU.store', ['unit' => 'SPBU']), $payload);

        $response->assertRedirect();
        
        $document = Document::where('doc_number', 'SOP-HC-2026')->firstOrFail();
        echo "   [SUKSES] Dokumen dibuat dengan ID: {$document->id}, Status: {$document->status}\n";

        echo "3. Creator (Imam M) memberikan Creator Approval...\n";
        $response = $this->actingAs($creator)
            ->post(route('reviewer.approve', ['id' => $document->id]), [
                'notes' => 'SOP HC Baru siap untuk ditinjau oleh para reviewer.'
            ]);
        $response->assertRedirect();
        $document->refresh();

        echo "4. Reviewer 1 (Trinwetty) menyetujui (APPROVE)...\n";
        $response = $this->actingAs($reviewer1)
            ->post(route('reviewer.approve', ['id' => $document->id]), [
                'notes' => 'Format dan tata bahasa sudah sesuai.'
            ]);
        $response->assertRedirect();

        echo "5. Reviewer 2 (Yayu Indah Maya) menyetujui (APPROVE)...\n";
        $response = $this->actingAs($reviewer2)
            ->post(route('reviewer.approve', ['id' => $document->id]), [
                'notes' => 'Konten isi sudah oke.'
            ]);
        $response->assertRedirect();

        echo "6. Reviewer 3 (Putri Larasati) meminta revisi (REJECT)...\n";
        $response = $this->actingAs($reviewer3)
            ->post(route('reviewer.reject', ['id' => $document->id]), [
                'notes' => 'Tolong sesuaikan bagian isi bab 2 mengenai kompensasi.'
            ]);
        $response->assertRedirect();
        $document->refresh();
        echo "   [SUKSES] Status dokumen saat ini: {$document->status} (Menunggu Revisi)\n";

        echo "7. Creator (via Admin) mengunggah berkas revisi...\n";
        $revisedIsi = $this->createMockPdf('SOP HC BARU Isi Revised', 2);
        $response = $this->actingAs($admin)
            ->put(route('admin.BU.update_revision', ['id' => $document->id]), [
                'title' => 'SOP HC BARU',
                'file_isi' => $revisedIsi,
            ]);
        $response->assertRedirect();
        $document->refresh();
        echo "   [SUKSES] Status setelah resubmit: {$document->status} (Menunggu Review Kembali)\n";

        // Double check Putri is current reviewer again
        $putriApproval = DocumentApproval::where('document_id', $document->id)
            ->where('user_id', $reviewer3->id)
            ->firstOrFail();
        $this->assertEquals('current', $putriApproval->status);

        echo "8. Reviewer 3 (Putri Larasati) menyetujui berkas revisi (APPROVE)...\n";
        $response = $this->actingAs($reviewer3)
            ->post(route('reviewer.approve', ['id' => $document->id]), [
                'notes' => 'Revisi bab 2 sudah tepat. Approve.'
            ]);
        $response->assertRedirect();

        echo "9. Final Approver (Zikri) memberikan persetujuan akhir (FINAL APPROVE)...\n";
        $response = $this->actingAs($finalApprover)
            ->post(route('reviewer.approve', ['id' => $document->id]), [
                'notes' => 'Dokumen sah dan aktif.'
            ]);
        $response->assertRedirect();
        $document->refresh();

        echo "10. Memvalidasi status dokumen akhir...\n";
        $this->assertEquals('active', $document->status);
        echo "   [SUKSES] Status Akhir Dokumen: {$document->status} (DOKUMEN AKTIF & SAH)\n";

        // Cleanup temporary mock files from OS temp dir
        foreach ($this->filesToClean as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}

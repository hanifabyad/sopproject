<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DocumentEvaluationTest extends TestCase
{
    public function test_sop_evaluation_lifecycle_and_security()
    {
        Mail::fake();
        DB::beginTransaction();

        try {
            // 1. Setup users
            $admin = User::where('role', 'admin')->first() ?: User::create([
                'username' => 'admin_test',
                'email' => 'admin_test@eqms.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status' => true
            ]);

            $evaluatorSPBU = User::where('role', 'Ka. BU SPBU')->first() ?: User::create([
                'username' => 'kabu_spbu_test',
                'email' => 'spbu_test@eqms.com',
                'password' => bcrypt('password123'),
                'role' => 'Ka. BU SPBU',
                'status' => true
            ]);

            $evaluatorHC = User::where('role', 'KA.DEPT.HC')->first() ?: User::create([
                'username' => 'kadept_hc_test',
                'email' => 'hc_test@eqms.com',
                'password' => bcrypt('password123'),
                'role' => 'KA.DEPT.HC',
                'status' => true
            ]);

            // 2. Create active SPBU Document older than 1 year (no effective_date yet)
            $document = Document::create([
                'title' => 'SOP Operasional SPBU',
                'department' => 'SPBU',
                'status' => 'active',
                'doc_number' => 'SOP-SPBU-001',
                'doc_revision' => '0',
                'doc_date' => '2025-08-10',
                'effective_date' => now()->subYear()->subDay()->toDateString(),
                'evaluation_due_date' => now()->subDay()->toDateString(),
            ]);

            // We explicitly overwrite created_at to trigger the 1-year logic
            DB::table('documents')->where('id', $document->id)->update([
                'created_at' => now()->subMonths(13),
                'updated_at' => now()->subMonths(13),
            ]);

            $document->refresh();

            // 3. Run artisan check-evaluations command
            Artisan::call('eqms:check-evaluations');

            // Assert document's effective date and evaluation status was initialized
            $document->refresh();
            $this->assertNotEmpty($document->effective_date);
            $this->assertEquals('overdue', $document->evaluation_status);
            $this->assertNotEmpty($document->evaluation_due_date);

            // Assert evaluation record exists
            $evaluation = Evaluation::where('document_id', $document->id)->first();
            $this->assertNotNull($evaluation);
            $this->assertEquals('overdue', $evaluation->status);
            $this->assertEquals($evaluatorSPBU->id, $evaluation->evaluator_id);

            // 4. Test Security: Evaluator HC tries to view SPBU evaluation (Expects 403)
            $response = $this->actingAs($evaluatorHC)
                ->get(route('evaluations.show', $evaluation->id));
            $response->assertStatus(403);

            // 5. Test Access: Evaluator SPBU views the evaluation (Expects 200, status becomes in_review)
            $response = $this->actingAs($evaluatorSPBU)
                ->get(route('evaluations.show', $evaluation->id));
            $response->assertStatus(200);

            $evaluation->refresh();
            $this->assertEquals('in_review', $evaluation->status);

            // 6. Test Submit: Evaluator SPBU submits the evaluation
            $payload = [
                'usage_status' => 'Digunakan secara rutin',
                'conformity_status' => 'Sesuai',
                'process_change_status' => 'Tidak ada',
                'effectiveness_status' => 'Sangat efektif',
                'implementation_issues' => 'Tidak ada kendala berarti.',
                'recommendation' => 'Pertahankan SOP ini.',
                'result' => 'CONTINUE',
            ];

            $response = $this->actingAs($evaluatorSPBU)
                ->post(route('evaluations.submit', $evaluation->id), $payload);
            $response->assertRedirect();

            $evaluation->refresh();
            $this->assertEquals('submitted', $evaluation->status);
            $this->assertEquals('CONTINUE', $evaluation->result);

            // 7. Test Admin Review: Admin views index and resolves evaluation as CONTINUE
            $response = $this->actingAs($admin)
                ->get(route('admin.evaluations.index'));
            $response->assertStatus(200);

            $response = $this->actingAs($admin)
                ->get(route('admin.evaluations.show', $evaluation->id));
            $response->assertStatus(200);

            // Admin resolves
            $response = $this->actingAs($admin)
                ->post(route('admin.evaluations.resolve', $evaluation->id), [
                    'result' => 'CONTINUE',
                    'admin_notes' => 'Disetujui. SOP dilanjutkan ke periode berikutnya.',
                ]);
            $response->assertRedirect();

            $evaluation->refresh();
            $document->refresh();
            $this->assertEquals('completed', $evaluation->status);
            $this->assertEquals('completed', $document->evaluation_status);
                        // Due date should be updated to 1 year later
            $expectedNextDueDate = date('Y-m-d', strtotime($evaluation->due_date->toDateString() . ' +1 year'));
            $this->assertEquals($expectedNextDueDate, $document->evaluation_due_date->toDateString());

            // 8. Test REVISION REQUIRED resolution
            // Let's create a new evaluation for another document
            $doc2 = Document::create([
                'title' => 'SOP Rekrutmen HC',
                'department' => 'HC',
                'status' => 'active',
                'doc_number' => 'SOP-HC-002',
                'doc_revision' => '0',
                'doc_date' => '2025-08-10',
                'effective_date' => now()->subYear()->subDay()->toDateString(),
                'evaluation_due_date' => now()->subDay()->toDateString(),
            ]);
            DB::table('documents')->where('id', $doc2->id)->update([
                'created_at' => now()->subMonths(13),
                'updated_at' => now()->subMonths(13),
            ]);

            Artisan::call('eqms:check-evaluations');
            $eval2 = Evaluation::where('document_id', $doc2->id)->firstOrFail();

            // Submit as HC Evaluator
            $this->actingAs($evaluatorHC)
                ->post(route('evaluations.submit', $eval2->id), [
                    'usage_status' => 'Digunakan tetapi terdapat kendala',
                    'conformity_status' => 'Sebagian perlu diperbarui',
                    'conformity_notes' => 'Form rekrutmen sudah usang.',
                    'process_change_status' => 'Ada',
                    'process_change_notes' => 'Ada tambahan tes psikologi online.',
                    'effectiveness_status' => 'Cukup efektif',
                    'implementation_issues' => 'User kesulitan karena form manual.',
                    'recommendation' => 'Perlu revisi formulir.',
                    'result' => 'REVISION REQUIRED',
                ]);

            // Admin resolves as REVISION REQUIRED
            $this->actingAs($admin)
                ->post(route('admin.evaluations.resolve', $eval2->id), [
                    'result' => 'REVISION REQUIRED',
                    'admin_notes' => 'Disetujui. Kirim ke need_revision untuk diperbaiki creator.',
                ]);

            $doc2->refresh();
            $eval2->refresh();
            $this->assertEquals('completed', $eval2->status);
            $this->assertEquals('need_revision', $doc2->status);
            $this->assertEquals($eval2->id, $doc2->evaluation_id);

        } finally {
            DB::rollBack();
        }
    }
}

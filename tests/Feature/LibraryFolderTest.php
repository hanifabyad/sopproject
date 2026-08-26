<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LibraryFolder;
use App\Models\LibraryFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LibraryFolderTest extends TestCase
{
    public function test_admin_can_manage_library_folders_and_files()
    {
        $filePath = null;
        DB::beginTransaction();

        try {
            $admin = User::where('role', 'admin')->firstOrFail();
            $user = User::where('role', '!=', 'admin')->firstOrFail();

            // 1. Admin creates a root folder
            $response = $this->actingAs($admin)
                ->post(route('admin.library.folder.create'), [
                    'name' => 'Formulir E2E',
                    'parent_id' => null,
                ]);

            $response->assertRedirect();
            $folder = LibraryFolder::where('name', 'Formulir E2E')->firstOrFail();
            $this->assertNull($folder->parent_id);

            // 2. Admin creates a subfolder
            $response = $this->actingAs($admin)
                ->post(route('admin.library.folder.create'), [
                    'name' => 'Sub Formulir E2E',
                    'parent_id' => $folder->id,
                ]);

            $response->assertRedirect();
            $subfolder = LibraryFolder::where('name', 'Sub Formulir E2E')->firstOrFail();
            $this->assertEquals($folder->id, $subfolder->parent_id);

            // 3. Non-admin user CANNOT create a folder
            $response = $this->actingAs($user)
                ->post(route('admin.library.folder.create'), [
                    'name' => 'Folder Hacking',
                    'parent_id' => null,
                ]);

            $response->assertStatus(403);
            $this->assertDatabaseMissing('library_folders', ['name' => 'Folder Hacking']);

            // 4. Admin uploads arbitrary DOCX file to subfolder
            $docx = UploadedFile::fake()->create('form_cuti.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $response = $this->actingAs($admin)
                ->post(route('admin.library.folder.upload', $subfolder->id), [
                    'file' => $docx,
                ]);

            $response->assertRedirect();
            $file = LibraryFile::where('name', 'form_cuti.docx')->firstOrFail();
            $this->assertEquals($subfolder->id, $file->folder_id);
            Storage::disk('public')->assertExists($file->path);

            // 5. Non-admin user CANNOT upload a file
            $response = $this->actingAs($user)
                ->post(route('admin.library.folder.upload', $subfolder->id), [
                    'file' => $docx,
                ]);

            $response->assertStatus(403);

            // 6. Admin deletes the parent folder and deletes physical files via cascade delete
            $filePath = $file->path;
            $response = $this->actingAs($admin)
                ->delete(route('admin.library.folder.destroy', $folder->id));

            $response->assertRedirect();
            $this->assertDatabaseMissing('library_folders', ['id' => $folder->id]);
            $this->assertDatabaseMissing('library_folders', ['id' => $subfolder->id]);
            $this->assertDatabaseMissing('library_files', ['id' => $file->id]);
            $this->assertFalse(Storage::disk('public')->exists($filePath));

        } finally {
            DB::rollBack();
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }
    }
}

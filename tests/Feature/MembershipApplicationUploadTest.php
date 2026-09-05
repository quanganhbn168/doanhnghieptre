<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MembershipApplicationUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public_media');
    }

    #[DataProvider('acceptedDocuments')]
    public function test_pdf_and_word_documents_are_accepted(string $name, string $mime, int $size): void
    {
        $this->post(route('membership.store'), $this->payload(UploadedFile::fake()->create($name, $size, $mime)))
            ->assertSessionHasNoErrors()->assertRedirect(route('account.dashboard'));

        $document = Business::query()->sole()->getFirstMedia('signed_membership_application');
        $this->assertNotNull($document);
        $this->assertSame('local', $document->disk);
        $this->assertStringEndsWith('.'.strtolower(pathinfo($name, PATHINFO_EXTENSION)), $document->file_name);
    }

    public static function acceptedDocuments(): array
    {
        return [
            'PDF at the size limit' => ['don.PDF', 'application/pdf', 10 * 1024],
            'Word DOC' => ['don.doc', 'application/msword', 20],
            'Word DOCX' => ['don.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 20],
        ];
    }

    public function test_actual_docx_content_is_detected_and_saved_with_the_correct_extension(): void
    {
        $temporaryFile = UploadedFile::fake()->createWithContent('don.docx', file_get_contents(public_path('downloads/don-gia-nhap-hoi-082026.docx')));
        // Use the real UploadedFile so MIME comes from file content, not the fake's filename.
        $upload = new UploadedFile($temporaryFile->getPathname(), 'don.docx', 'application/octet-stream', null, true);

        $this->post(route('membership.store'), $this->payload($upload))->assertSessionHasNoErrors();

        $business = Business::query()->sole();
        $document = $business->getFirstMedia('signed_membership_application');
        $this->assertSame('don-gia-nhap-hoi-da-ky-'.$business->id.'.docx', $document->file_name);
        $this->assertSame('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $document->mime_type);
        $this->get(route('business.membership-application.download', $business))->assertDownload($document->file_name);
    }

    #[DataProvider('invalidDocuments')]
    public function test_disallowed_content_and_extensions_are_rejected_even_with_a_forged_client_mime(string $name, string $content): void
    {
        $temporaryFile = UploadedFile::fake()->createWithContent($name, $content);
        $upload = new UploadedFile($temporaryFile->getPathname(), $name, 'application/pdf', null, true);

        $this->from(route('membership.create'))->post(route('membership.store'), $this->payload($upload))
            ->assertSessionHasErrors(['membership_application' => 'Đơn gia nhập Hội chỉ nhận tệp PDF, DOC hoặc DOCX hợp lệ.'])
            ->assertRedirect(route('membership.create'));

        $this->assertDatabaseCount('businesses', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('media', 0);
        $this->get(route('membership.create'))->assertSee('Đơn gia nhập Hội chỉ nhận tệp PDF, DOC hoặc DOCX hợp lệ.');
    }

    public static function invalidDocuments(): array
    {
        $pdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n";
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN1sAAAAASUVORK5CYII=');

        return [
            'PNG image' => ['don.png', $image],
            'image renamed PDF' => ['don.pdf', $image],
            'script renamed PDF' => ['don.pdf', '<?php echo "not a document";'],
            'text renamed DOCX' => ['don.docx', 'This is plain text, not a Word document.'],
            'PDF with executable extension' => ['don.exe', $pdf],
            'PDF with archive extension' => ['don.zip', $pdf],
            'PDF without extension' => ['don', $pdf],
        ];
    }

    public function test_oversized_documents_are_rejected(): void
    {
        $this->post(route('membership.store'), $this->payload(UploadedFile::fake()->create('don.pdf', 10 * 1024 + 1, 'application/pdf')))
            ->assertSessionHasErrors(['membership_application' => 'Đơn gia nhập Hội không được vượt quá 10 MB.']);
        $this->assertDatabaseCount('businesses', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_replacement_preserves_the_existing_document_and_application(): void
    {
        $owner = User::factory()->create(['approval_status' => 'approved', 'is_active' => true]);
        $business = Business::query()->create([
            'name' => 'Hồ sơ đang bổ sung', 'slug' => 'upload-replacement', 'tax_code' => 'UPLOAD-001',
            'status' => 'rejected', 'submitted_by_user_id' => $owner->id,
        ]);
        $original = $business->addMedia(UploadedFile::fake()->create('don-cu.pdf', 20, 'application/pdf'))
            ->toMediaCollection('signed_membership_application');
        $payload = $this->payload(UploadedFile::fake()->image('don.png'));

        $this->actingAs($owner)->patch(route('account.businesses.update', $business), $payload)
            ->assertSessionHasErrors('membership_application');

        $this->assertSame('rejected', $business->fresh()->status);
        $this->assertSame('Hồ sơ đang bổ sung', $business->fresh()->name);
        $this->assertSame($original->id, $business->fresh()->getFirstMedia('signed_membership_application')->id);
        Storage::disk('local')->assertExists($original->getPathRelativeToRoot());

        unset($payload['membership_application']);
        $this->patch(route('account.businesses.update', $business), $payload)->assertSessionHasNoErrors();
        $this->assertSame($original->id, $business->fresh()->getFirstMedia('signed_membership_application')->id);
    }

    private function payload(UploadedFile $document): array
    {
        $industry = Industry::query()->firstOrCreate(['slug' => 'upload-test-group'], ['name' => 'Khối kiểm thử', 'is_active' => true, 'is_member_group' => true]);

        return [
            'name' => 'Doanh nghiệp kiểm thử đơn', 'tax_code' => 'UPLOAD-001', 'business_type' => 'limited', 'business_size' => 'small',
            'representative_name' => 'Đại diện kiểm thử', 'industry_ids' => [$industry->id], 'phone' => '0900000000', 'email' => 'company@example.test',
            'login_email' => 'upload@example.test', 'password' => 'membership-test-123', 'password_confirmation' => 'membership-test-123',
            'address' => 'Địa chỉ kiểm thử', 'province' => 'Bắc Ninh', 'summary' => 'Hồ sơ kiểm thử tải đơn.',
            'membership_application' => $document, 'confirm_information' => '1',
        ];
    }
}

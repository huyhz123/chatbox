<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function file_can_be_created()
    {
        $file = File::factory()->create([
            'name' => 'Test PDF',
            'file_path' => 'uploads/test.pdf',
        ]);

        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'name' => 'Test PDF',
        ]);
    }

    /** @test */
    public function file_has_required_attributes()
    {
        $file = File::factory()->create([
            'name' => 'Tutorial PDF',
            'file_type' => 'pdf',
            'file_size' => 2048000,
            'price' => 100000,
            'is_active' => true,
        ]);

        $this->assertEquals('Tutorial PDF', $file->name);
        $this->assertEquals('pdf', $file->file_type);
        $this->assertEquals(2048000, $file->file_size);
        $this->assertEquals(100000, $file->price);
        $this->assertTrue($file->is_active);
    }

    /** @test */
    public function active_scope_filters_active_files()
    {
        File::factory()->create(['is_active' => true]);
        File::factory()->create(['is_active' => true]);
        File::factory()->create(['is_active' => false]);

        $activeFiles = File::active()->get();

        $this->assertEquals(2, $activeFiles->count());
    }

    /** @test */
    public function featured_scope_filters_featured_files()
    {
        File::factory()->create(['is_featured' => true]);
        File::factory()->create(['is_featured' => false]);

        $featuredFiles = File::featured()->get();

        $this->assertEquals(1, $featuredFiles->count());
        $this->assertTrue($featuredFiles->first()->is_featured);
    }

    /** @test */
    public function get_current_price_returns_special_price_if_available()
    {
        $file = File::factory()->create([
            'price' => 150000,
            'special_price' => 99000,
        ]);

        $this->assertEquals(99000, $file->getCurrentPrice());
    }

    /** @test */
    public function get_current_price_returns_regular_price_if_no_special_price()
    {
        $file = File::factory()->create([
            'price' => 150000,
            'special_price' => null,
        ]);

        $this->assertEquals(150000, $file->getCurrentPrice());
    }

    /** @test */
    public function get_file_size_formatted_returns_bytes()
    {
        $file = File::factory()->create(['file_size' => 512]);

        $this->assertStringContainsString('B', $file->getFileSizeFormatted());
    }

    /** @test */
    public function get_file_size_formatted_returns_kilobytes()
    {
        $file = File::factory()->create(['file_size' => 2048]);

        $formatted = $file->getFileSizeFormatted();
        $this->assertStringContainsString('KB', $formatted);
    }

    /** @test */
    public function get_file_size_formatted_returns_megabytes()
    {
        $file = File::factory()->create(['file_size' => 2097152]);

        $formatted = $file->getFileSizeFormatted();
        $this->assertStringContainsString('MB', $formatted);
    }

    /** @test */
    public function get_file_size_formatted_returns_gigabytes()
    {
        $file = File::factory()->create(['file_size' => 1073741824]);

        $formatted = $file->getFileSizeFormatted();
        $this->assertStringContainsString('GB', $formatted);
    }

    /** @test */
    public function increment_sold_count_increases_count()
    {
        $file = File::factory()->create(['sold_count' => 5]);

        $file->incrementSoldCount();

        $this->assertEquals(6, $file->refresh()->sold_count);
    }

    /** @test */
    public function increment_download_count_increases_count()
    {
        $file = File::factory()->create(['download_count' => 10]);

        $file->incrementDownloadCount();

        $this->assertEquals(11, $file->refresh()->download_count);
    }

    /** @test */
    public function file_belongs_to_category()
    {
        $category = Category::factory()->create();
        $file = File::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $file->category);
        $this->assertEquals($category->id, $file->category->id);
    }

    /** @test */
    public function file_can_have_multiple_downloads()
    {
        $file = File::factory()->create();

        $this->assertEquals(0, $file->downloads()->count());
    }

    /** @test */
    public function file_can_have_order_items()
    {
        $file = File::factory()->create();

        $this->assertEquals(0, $file->orderItems()->count());
    }

    /** @test */
    public function file_soft_delete_works()
    {
        $file = File::factory()->create();
        $fileId = $file->id;

        $file->delete();

        $this->assertNull(File::find($fileId));
        $this->assertNotNull(File::withTrashed()->find($fileId));
    }

    /** @test */
    public function file_price_is_cast_to_decimal()
    {
        $file = File::factory()->create(['price' => '199.99']);

        $this->assertIsNumeric($file->price);
    }

    /** @test */
    public function file_size_is_cast_to_integer()
    {
        $file = File::factory()->create(['file_size' => '1024000']);

        $this->assertIsInt($file->file_size);
    }

    /** @test */
    public function active_and_featured_scopes_can_be_chained()
    {
        File::factory()->create(['is_active' => true, 'is_featured' => true]);
        File::factory()->create(['is_active' => true, 'is_featured' => false]);
        File::factory()->create(['is_active' => false, 'is_featured' => true]);

        $files = File::active()->featured()->get();

        $this->assertEquals(1, $files->count());
    }

    /** @test */
    public function file_with_download_limit_constraint()
    {
        $file = File::factory()->create([
            'download_limit' => 5,
            'sold_count' => 3,
        ]);

        $this->assertEquals(5, $file->download_limit);
        $this->assertEquals(3, $file->sold_count);
    }

    /** @test */
    public function file_can_be_encrypted()
    {
        $file = File::factory()->create([
            'is_encrypted' => true,
            'file_path' => 'uploads/encrypted.bin',
        ]);

        $this->assertTrue($file->is_encrypted);
    }

    /** @test */
    public function file_can_have_preview_url()
    {
        $file = File::factory()->create([
            'preview_url' => 'https://example.com/preview.pdf',
        ]);

        $this->assertEquals('https://example.com/preview.pdf', $file->preview_url);
    }

    /** @test */
    public function multiple_files_increment_downloads_independently()
    {
        $file1 = File::factory()->create(['download_count' => 5]);
        $file2 = File::factory()->create(['download_count' => 10]);

        $file1->incrementDownloadCount();
        $file2->incrementDownloadCount();

        $this->assertEquals(6, $file1->refresh()->download_count);
        $this->assertEquals(11, $file2->refresh()->download_count);
    }

    /** @test */
    public function file_version_tracking()
    {
        $file = File::factory()->create([
            'name' => 'Document v1.0',
            'version' => '1.0',
        ]);

        $this->assertEquals('1.0', $file->version);
    }

    /** @test */
    public function file_soft_deleted_not_returned_by_active_scope()
    {
        $file = File::factory()->create(['is_active' => true]);

        $file->delete();

        $activeFiles = File::active()->get();

        $this->assertEquals(0, $activeFiles->count());
    }
}

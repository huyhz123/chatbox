<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function service_can_be_created()
    {
        $service = Service::factory()->create([
            'name' => 'Test Service',
            'slug' => 'test-service',
            'price' => 100000,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Test Service',
        ]);
    }

    /** @test */
    public function service_has_required_attributes()
    {
        $service = Service::factory()->create([
            'name' => 'Premium Service',
            'price' => 250000,
            'cost' => 100000,
            'is_active' => true,
        ]);

        $this->assertEquals('Premium Service', $service->name);
        $this->assertEquals(250000, $service->price);
        $this->assertEquals(100000, $service->cost);
        $this->assertTrue($service->is_active);
    }

    /** @test */
    public function active_scope_filters_active_services()
    {
        Service::factory()->create(['is_active' => true]);
        Service::factory()->create(['is_active' => true]);
        Service::factory()->create(['is_active' => false]);

        $activeServices = Service::active()->get();

        $this->assertEquals(2, $activeServices->count());
        $this->assertTrue($activeServices->every(fn($s) => $s->is_active));
    }

    /** @test */
    public function featured_scope_filters_featured_services()
    {
        Service::factory()->create(['is_featured' => true]);
        Service::factory()->create(['is_featured' => true]);
        Service::factory()->create(['is_featured' => false]);

        $featuredServices = Service::featured()->get();

        $this->assertEquals(2, $featuredServices->count());
        $this->assertTrue($featuredServices->every(fn($s) => $s->is_featured));
    }

    /** @test */
    public function ordered_scope_orders_by_order_field()
    {
        Service::factory()->create(['order' => 3]);
        Service::factory()->create(['order' => 1]);
        Service::factory()->create(['order' => 2]);

        $orderedServices = Service::ordered()->get();

        $this->assertEquals(1, $orderedServices->first()->order);
        $this->assertEquals(2, $orderedServices[1]->order);
        $this->assertEquals(3, $orderedServices->last()->order);
    }

    /** @test */
    public function get_current_price_returns_special_price_if_available()
    {
        $service = Service::factory()->create([
            'price' => 100000,
            'special_price' => 80000,
        ]);

        $this->assertEquals(80000, $service->getCurrentPrice());
    }

    /** @test */
    public function get_current_price_returns_regular_price_if_no_special_price()
    {
        $service = Service::factory()->create([
            'price' => 100000,
            'special_price' => null,
        ]);

        $this->assertEquals(100000, $service->getCurrentPrice());
    }

    /** @test */
    public function get_profit_calculates_correctly()
    {
        $service = Service::factory()->create([
            'price' => 100000,
            'cost' => 40000,
            'special_price' => null,
        ]);

        $expectedProfit = 100000 - 40000;
        $this->assertEquals($expectedProfit, $service->getProfit());
    }

    /** @test */
    public function get_profit_uses_special_price_when_available()
    {
        $service = Service::factory()->create([
            'price' => 100000,
            'special_price' => 75000,
            'cost' => 30000,
        ]);

        $expectedProfit = 75000 - 30000;
        $this->assertEquals($expectedProfit, $service->getProfit());
    }

    /** @test */
    public function increment_sold_count_increases_count_by_one()
    {
        $service = Service::factory()->create(['sold_count' => 5]);

        $service->incrementSoldCount();

        $this->assertEquals(6, $service->refresh()->sold_count);
    }

    /** @test */
    public function increment_sold_count_increases_by_specified_quantity()
    {
        $service = Service::factory()->create(['sold_count' => 5]);

        $service->incrementSoldCount(10);

        $this->assertEquals(15, $service->refresh()->sold_count);
    }

    /** @test */
    public function service_belongs_to_category()
    {
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $service->category);
        $this->assertEquals($category->id, $service->category->id);
    }

    /** @test */
    public function service_can_have_multiple_tickets()
    {
        $service = Service::factory()->create();

        $this->assertEquals(0, $service->tickets()->count());
    }

    /** @test */
    public function service_can_have_order_items()
    {
        $service = Service::factory()->create();

        $this->assertEquals(0, $service->orderItems()->count());
    }

    /** @test */
    public function service_soft_delete_works()
    {
        $service = Service::factory()->create();
        $serviceId = $service->id;

        $service->delete();

        $this->assertNull(Service::find($serviceId));
        $this->assertNotNull(Service::withTrashed()->find($serviceId));
    }

    /** @test */
    public function service_fills_required_fields()
    {
        $attributes = [
            'name' => 'API Service',
            'price' => 50000,
            'cost' => 20000,
            'api_provider' => 'dhru',
            'is_active' => true,
        ];

        $service = Service::factory()->create($attributes);

        $this->assertEquals('API Service', $service->name);
        $this->assertEquals('dhru', $service->api_provider);
    }

    /** @test */
    public function active_and_featured_scopes_can_be_chained()
    {
        Service::factory()->create(['is_active' => true, 'is_featured' => true]);
        Service::factory()->create(['is_active' => true, 'is_featured' => false]);
        Service::factory()->create(['is_active' => false, 'is_featured' => true]);

        $services = Service::active()->featured()->get();

        $this->assertEquals(1, $services->count());
        $this->assertTrue($services->first()->is_active);
        $this->assertTrue($services->first()->is_featured);
    }

    /** @test */
    public function service_price_is_cast_to_decimal()
    {
        $service = Service::factory()->create(['price' => '100.50']);

        $this->assertIsNumeric($service->price);
        $this->assertEquals('100.50', (string)$service->price);
    }

    /** @test */
    public function service_images_are_cast_to_array()
    {
        $images = ['image1.jpg', 'image2.jpg'];
        $service = Service::factory()->create(['images' => $images]);

        $this->assertIsArray($service->images);
        $this->assertEquals($images, $service->images);
    }

    /** @test */
    public function service_required_fields_are_cast_to_array()
    {
        $requiredFields = ['phone', 'email'];
        $service = Service::factory()->create(['required_fields' => $requiredFields]);

        $this->assertIsArray($service->required_fields);
        $this->assertEquals($requiredFields, $service->required_fields);
    }
}

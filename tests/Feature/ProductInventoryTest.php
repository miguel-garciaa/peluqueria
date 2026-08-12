<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_create_edit_and_delete_an_inventory_product(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'image_path' => $this->fakePng('secador.png'),
                'name' => 'Secador profesional',
                'sku' => 'MAQ-SEC-01',
                'category' => 'machinery',
                'description' => 'Secador iónico para puestos de trabajo.',
                'price' => 189.90,
                'units' => 4,
                'low_stock_threshold' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('sku', 'MAQ-SEC-01')->firstOrFail();

        $this->assertSame('Secador profesional', $product->name);
        $this->assertSame(4, $product->units);
        $this->assertSame('189.90', $product->price);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'name' => 'Secador profesional iónico',
                'units' => 2,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Secador profesional iónico',
            'units' => 2,
        ]);

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords([$product])
            ->callTableAction('delete', $product);

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing($product->image_path);
    }

    public function test_low_stock_only_includes_products_at_or_below_their_threshold(): void
    {
        Product::query()->create([
            'name' => 'Champú',
            'category' => 'hair_care',
            'price' => 18,
            'units' => 3,
            'low_stock_threshold' => 3,
            'is_active' => true,
        ]);
        Product::query()->create([
            'name' => 'Plancha',
            'category' => 'machinery',
            'price' => 120,
            'units' => 5,
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        $this->assertSame(['Champú'], Product::query()->lowStock()->pluck('name')->all());
    }

    public function test_product_form_rejects_invalid_inventory_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Producto inválido',
                'category' => 'hair_care',
                'price' => -1,
                'units' => -2,
                'low_stock_threshold' => -1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'price' => 'min',
                'units' => 'min',
                'low_stock_threshold' => 'min',
            ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true),
        );
    }
}

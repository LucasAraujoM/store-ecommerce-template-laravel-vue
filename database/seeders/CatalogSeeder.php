<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Unsplash photo ids used for the demo catalog.
     */
    private const IMAGES = [
        'dress-a' => '1595777457583-95e059d581b8',
        'dress-b' => '1566174053879-31528523f8ae',
        'dress-c' => '1572804013309-59a88b7e92f1',
        'dress-d' => '1515372039744-b8f02a3ae446',
        'editorial' => '1496747611176-843222e1e57c',
        'dress-e' => '1539008835657-9e8e9680c956',
        'blouse' => '1585487000160-6ebcfceb0d03',
        'pants' => '1594633312681-425c7b97ccd1',
        'jeans' => '1541099649105-f69ad21f3246',
        'coat' => '1591369822096-ffd140ec948f',
        'coat-b' => '1544022613-e87ca75a784a',
        'bag' => '1520903920243-00d872a2d1c9',
        'bag-b' => '1591561954557-26941169b49e',
        'jewelry' => '1611652022419-a9419f74343d',
    ];

    public function run(): void
    {
        $catalog = $this->catalog();

        foreach ($catalog as $categoryData) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'image' => $this->image($categoryData['image']),
                    'is_active' => true,
                    'sort_order' => $categoryData['sort_order'],
                ],
            );

            foreach ($categoryData['products'] as $productData) {
                $this->createProduct($category, $productData);
            }
        }

        $this->createDemoOrders();
    }

    private function createProduct(Category $category, array $data): void
    {
        $product = Product::updateOrCreate(
            ['slug' => Str::slug($data['name'])],
            [
                'category_id' => $category->id,
                'name' => $data['name'],
                'short_description' => $data['short'],
                'description' => $data['description'],
                'price_cents' => $data['price'] * 100,
                'compare_at_price_cents' => isset($data['compare']) ? $data['compare'] * 100 : null,
                'is_active' => true,
                'is_featured' => $data['featured'] ?? false,
            ],
        );

        foreach ($data['images'] as $index => $image) {
            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'path' => $this->image($image),
                ],
                [
                    'alt' => $data['name'],
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ],
            );
        }

        $sizes = $data['sizes'] ?? ['XS', 'S', 'M', 'L'];
        $colors = $data['colors'] ?? [['Negro', '#1c1a19'], ['Crema', '#efe6d8'], ['Rosé', '#d8a7a1']];

        foreach ($sizes as $size) {
            foreach ($colors as [$color, $hex]) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'size' => $size,
                        'color' => $color,
                    ],
                    [
                        'color_hex' => $hex,
                        'sku' => 'LUN-'.str_pad((string) $product->id, 3, '0', STR_PAD_LEFT).'-'.Str::upper(Str::slug($size.'-'.$color)),
                        'stock' => $data['stock'] ?? 6,
                        'price_cents' => null,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /**
     * Demo orders so the admin dashboard has meaningful data.
     */
    private function createDemoOrders(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $variants = ProductVariant::query()
            ->with('product')
            ->where('stock', '>', 0)
            ->take(6)
            ->get();

        if ($variants->isEmpty()) {
            return;
        }

        $zone = ShippingZone::query()->where('is_pickup', false)->first();
        $customer = User::query()->where('email', 'cliente@lunaria.test')->first();

        if ($zone === null) {
            return;
        }

        $statuses = [
            [OrderStatus::Pending, PaymentStatus::Pending, 0],
            [OrderStatus::Paid, PaymentStatus::Paid, 2],
            [OrderStatus::Processing, PaymentStatus::Paid, 4],
            [OrderStatus::Shipped, PaymentStatus::Paid, 6],
            [OrderStatus::Delivered, PaymentStatus::Paid, 12],
        ];

        foreach ($statuses as $index => [$status, $paymentStatus, $daysAgo]) {
            $variant = $variants[$index % $variants->count()];
            $quantity = ($index % 2) + 1;
            $unitPrice = $variant->price_cents ?? $variant->product->price_cents;
            $subtotal = $unitPrice * $quantity;
            $shipping = 490000;
            $createdAt = now()->subDays($daysAgo);

            $order = Order::updateOrCreate(
                ['number' => 'LUN-DEMO-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $customer?->id,
                    'tracking_code' => 'TRK-DEMO'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'payment_method' => PaymentMethod::Transfer,
                    'payment_status' => $paymentStatus,
                    'customer_first_name' => 'Sofía',
                    'customer_last_name' => 'Martínez',
                    'customer_email' => 'cliente@lunaria.test',
                    'customer_phone' => '+54 11 5555-1234',
                    'customer_dni' => '35123456',
                    'shipping_method' => ShippingMethod::HomeDelivery,
                    'shipping_zone_id' => $zone->id,
                    'shipping_address' => 'Av. Santa Fe 3456, 4º B',
                    'shipping_city' => 'Ciudad Autónoma de Buenos Aires',
                    'shipping_province' => 'Ciudad Autónoma de Buenos Aires',
                    'shipping_postal_code' => '1425',
                    'subtotal_cents' => $subtotal,
                    'shipping_cost_cents' => $shipping,
                    'discount_cents' => 0,
                    'total_cents' => $subtotal + $shipping,
                    'notes' => null,
                    'paid_at' => $paymentStatus === PaymentStatus::Paid ? $createdAt : null,
                    'shipped_at' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true) ? $createdAt->copy()->addDay() : null,
                    'delivered_at' => $status === OrderStatus::Delivered ? $createdAt->copy()->addDays(3) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );

            $order->items()->updateOrCreate(
                ['order_id' => $order->id, 'sku' => $variant->sku],
                [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_size' => $variant->size,
                    'variant_color' => $variant->color,
                    'unit_price_cents' => $unitPrice,
                    'quantity' => $quantity,
                    'total_cents' => $subtotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );

            $order->statusHistories()->updateOrCreate(
                ['order_id' => $order->id, 'status' => $status],
                [
                    'created_by' => null,
                    'note' => 'Pedido de demostración',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );
        }
    }

    private function image(string $key): string
    {
        $id = self::IMAGES[$key];

        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&q=80";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'name' => 'Vestidos',
                'description' => 'Siluetas fluidas y románticas para cada ocasión.',
                'image' => 'dress-a',
                'sort_order' => 1,
                'products' => [
                    [
                        'name' => 'Vestido Amapola',
                        'short' => 'Vestido midi de gasa con estampa floral y cintura marcada.',
                        'description' => 'Un clásico de temporada. Confeccionado en gasa liviana, con mangas largas, escote en V y cintura ideal para realzar la silueta. Forrado en el cuerpo y con cierre invisible en la espalda.',
                        'price' => 58900,
                        'compare' => 69900,
                        'featured' => true,
                        'images' => ['dress-a', 'editorial'],
                        'colors' => [['Burdeos', '#6d2233'], ['Negro', '#1c1a19']],
                    ],
                    [
                        'name' => 'Vestido Medianoche',
                        'short' => 'Vestido largo de satén con espalda abierta y caída fluida.',
                        'description' => 'Pensado para las noches especiales. Satén de caída suave, breteles ajustables y espalda con abertura en V. Largo máximo y forro completo.',
                        'price' => 72500,
                        'featured' => true,
                        'images' => ['dress-b'],
                        'colors' => [['Negro', '#1c1a19'], ['Bordó', '#6d2233']],
                    ],
                    [
                        'name' => 'Vestido Jardín Secreto',
                        'short' => 'Vestido corto con mangas abullonadas y detalle de lazo.',
                        'description' => 'Femenino y fresco. Manga abullonada, escote cuadrado y lazo al frente. Ideal con sandalias planas o botas.',
                        'price' => 64900,
                        'images' => ['dress-c', 'dress-d'],
                        'colors' => [['Rosé', '#d8a7a1'], ['Crema', '#efe6d8']],
                    ],
                    [
                        'name' => 'Vestido Brisa',
                        'short' => 'Vestido lencero de tiras finas en viscosa.',
                        'description' => 'Liviano y versátil. Viscosa con caída, tiras regulables y corte al bies. Se lleva solo o con un blazer.',
                        'price' => 49900,
                        'images' => ['dress-d'],
                        'colors' => [['Champagne', '#d8c3a5'], ['Negro', '#1c1a19']],
                    ],
                ],
            ],
            [
                'name' => 'Tops y blusas',
                'description' => 'Básicos con detalles que elevan cualquier look.',
                'image' => 'blouse',
                'sort_order' => 2,
                'products' => [
                    [
                        'name' => 'Blusa Perla',
                        'short' => 'Blusa de seda con botones nacarados y puños suaves.',
                        'description' => 'Una pieza atemporal. Seda fluida, cuello clásico y botones nacarados. Combina perfecto con sastreros y jeans.',
                        'price' => 32900,
                        'images' => ['blouse', 'editorial'],
                        'colors' => [['Crema', '#efe6d8'], ['Negro', '#1c1a19']],
                    ],
                    [
                        'name' => 'Camisa Lino Arena',
                        'short' => 'Camisa oversize de lino con bolsillos frontales.',
                        'description' => 'Lino 100% natural, corte holgado y textura fresca. Una camisa que se convierte en aliada todo el año.',
                        'price' => 38900,
                        'featured' => true,
                        'images' => ['editorial'],
                        'colors' => [['Arena', '#cbb89d'], ['Blanco', '#f7f3ec']],
                    ],
                    [
                        'name' => 'Top Sedosa',
                        'short' => 'Top de satén con escote drapeado y tirantes finos.',
                        'description' => 'Satén suave con caída elegante y drapeado frontal. Ideal para una salida o para sumar textura a un look de día.',
                        'price' => 27900,
                        'images' => ['blouse'],
                        'colors' => [['Rosé', '#d8a7a1'], ['Negro', '#1c1a19']],
                    ],
                ],
            ],
            [
                'name' => 'Pantalones y polleras',
                'description' => 'Cortes que acompañan el cuerpo y duran temporadas.',
                'image' => 'pants',
                'sort_order' => 3,
                'products' => [
                    [
                        'name' => 'Pantalón Sastre Olivia',
                        'short' => 'Pantalón de pinza con pierna amplia y caída impecable.',
                        'description' => 'Sastrería suave con pinzas frontales, cintura alta y bolsillos laterales. Un básico que ordena cualquier outfit.',
                        'price' => 54900,
                        'images' => ['pants'],
                        'colors' => [['Arena', '#cbb89d'], ['Negro', '#1c1a19']],
                    ],
                    [
                        'name' => 'Jean Mom Fit',
                        'short' => 'Jean de tiro alto con calce relajado y ruedo crudo.',
                        'description' => 'Denim rígido de algodón, tiro alto y pierna recta. El calce favorito para todos los días.',
                        'price' => 47900,
                        'featured' => true,
                        'images' => ['jeans'],
                        'colors' => [['Celeste', '#9db6c9'], ['Negro', '#1c1a19']],
                    ],
                ],
            ],
            [
                'name' => 'Abrigos',
                'description' => 'Capas cálidas con terminaciones cuidadas.',
                'image' => 'coat',
                'sort_order' => 4,
                'products' => [
                    [
                        'name' => 'Trench Camel',
                        'short' => 'Trench de gabardina con cinto y solapas clásicas.',
                        'description' => 'El abrigo de entretiempo por excelencia. Gabardina liviana, cinto ajustable, bolsillos y forrería interior.',
                        'price' => 119900,
                        'compare' => 139900,
                        'featured' => true,
                        'images' => ['coat', 'coat-b'],
                        'colors' => [['Camel', '#b98d5f'], ['Negro', '#1c1a19']],
                    ],
                    [
                        'name' => 'Campera Borgoña',
                        'short' => 'Campera acolchada corta con cierre y bolsillos.',
                        'description' => 'Abrigo liviano y abrigado. Acolchado suave, cierre frontal y capucha desmontable. Ideal para los días fríos.',
                        'price' => 89900,
                        'images' => ['coat-b'],
                        'colors' => [['Bordó', '#6d2233'], ['Negro', '#1c1a19']],
                    ],
                ],
            ],
            [
                'name' => 'Accesorios',
                'description' => 'El detalle final que cambia todo.',
                'image' => 'bag',
                'sort_order' => 5,
                'products' => [
                    [
                        'name' => 'Cartera Mini Noir',
                        'short' => 'Mini cartera estructurada con cadena dorada.',
                        'description' => 'Compacta y elegante. Interior con división, cierre magnético y cadena desmontable para llevar al hombro o cruzada.',
                        'price' => 42900,
                        'featured' => true,
                        'images' => ['bag'],
                        'sizes' => ['Único'],
                        'colors' => [['Negro', '#1c1a19'], ['Bordó', '#6d2233']],
                    ],
                    [
                        'name' => 'Bolso Tejido',
                        'short' => 'Bolso de rafia tejida con asas de cuero.',
                        'description' => 'Textura natural y formato amplio para el día. Rafia tejida a mano, base firme y asas de cuero.',
                        'price' => 37900,
                        'images' => ['bag-b'],
                        'sizes' => ['Único'],
                        'colors' => [['Natural', '#cbb89d']],
                    ],
                    [
                        'name' => 'Aros Lumière',
                        'short' => 'Aros bañados en oro con cristales facetados.',
                        'description' => 'Luz propia. Aros livianos bañados en oro de 18k con cristales facetados. Apto para pieles sensibles.',
                        'price' => 18900,
                        'images' => ['jewelry'],
                        'sizes' => ['Único'],
                        'colors' => [['Dorado', '#c9a45c']],
                    ],
                ],
            ],
        ];
    }
}

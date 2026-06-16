<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Seed a realistic grocery catalog with local image placeholders.
     */
    public function run(): void
    {
        $products = [
            // Paste your own image URL in the last column. Leave null to show the POS fallback image.
            ['Beverages', 'Air Mineral 600ml', 'pcs', 2200, 3500, 120, 24, 'https://i0.wp.com/raisa.aeonstore.id/wp-content/uploads/2023/04/4462866.jpg?fit=582%2C800&ssl=1'],
            ['Beverages', 'Teh Botol 350ml', 'pcs', 3500, 5000, 88, 20, 'https://siliwangibolukukus.com/wp-content/uploads/2020/09/teh-botol.png'],
            ['Beverages', 'Kopi Susu Kaleng', 'pcs', 6500, 8500, 64, 15, 'https://www.nescafe.com/id/sites/default/files/styles/pdp_banner_image/public/2025-03/NESCAFE%20ID%20Premium%20CanMYW%20Original_Mockup-960.png.webp?itok=ioY_va2K'],
            ['Dairy & Eggs', 'Susu UHT Full Cream 1L', 'pcs', 15500, 19500, 42, 12, 'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/medium/catalog-image/MTA-97584382/no_brand_milk_life_uht_full_cream_1l_full01_n0srho25.webp'],
            ['Dairy & Eggs', 'Telur Ayam 1kg', 'kg', 24500, 28500, 36, 8, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR9RRuHc-v4u8lwNqBad4SbUQV46GwXX6pz_g0_Y17uiw&s=10'],
            ['Snacks & Confectionery', 'Keripik Kentang Original', 'pcs', 9000, 12500, 58, 14, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQV8bXB_7-842BXqXk-vaNrre_zvEUGh9wpemX85V65RHXTpNsvM2gLqdWd&s=10'],
            ['Snacks & Confectionery', 'Biskuit Cokelat', 'pcs', 7500, 10500, 72, 16, 'https://c.alfagift.id/product/1/1_A10160007094_20240403114530917_base.jpg'],
            ['Bakery & Bread', 'Roti Tawar Kupas', 'pcs', 12500, 16000, 25, 8, 'https://www.sarirotimakassar.com/wp-content/uploads/2020/07/Roti-Tawar-Kupas.png'],
            ['Fruits & Vegetables', 'Pisang Cavendish 1kg', 'kg', 16000, 22000, 30, 8, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2x775CuxaRE8mfmBhucwBsBMAdQ5B5Fr3Bw&s'],
            ['Fruits & Vegetables', 'Tomat Merah 1kg', 'kg', 9000, 14000, 24, 7, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4ckoqiaDR-Dho_PKnn7GX94xvkTpK65gLKAFXnwZ72w&s=10'],
            ['Meat & Seafood', 'Dada Ayam Fillet 1kg', 'kg', 42000, 55000, 18, 5, 'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full//101/MTA-7723379/afcbandung_daging_ayam_fillet_dada_1kg_boneless_1_kg_full01_gaq7k6zr.jpg'],
            ['Meat & Seafood', 'Ikan Tuna Fillet 500g', 'pcs', 32000, 41000, 16, 5, 'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full//87/MTA-7133207/bliss_kitchen_bliss_kitchen_tuna_steak_daging_segar_-500g-_full03_k680dcf3.jpg'],
            ['Frozen Foods', 'Nugget Ayam 500g', 'pcs', 27000, 34500, 28, 8, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT5PzIVLR51ux0m142zIu-HZU-Rvj3JkVuKdlKzonvIrw&s=10'],
            ['Condiments & Sauces', 'Saus Sambal 335ml', 'pcs', 10500, 14500, 46, 10, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTXe8qD9Xmbf_okQVso923XXTBcinS5VAXHU3R5zqzhZA&s=10'],
            ['Breakfast & Cereals', 'Sereal Jagung 250g', 'pcs', 18000, 23500, 22, 7, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTi4qSrqATnYv4IgIZIxmnKmxfKmXBu7n65wkwytWWPiw&s=10'],
            ['Personal Care', 'Sabun Mandi Cair 450ml', 'pcs', 18500, 24500, 34, 10, 'https://o-cdf.oramiland.com/unsafe/core.oramiland.com/media/products/226969/LYB0F38-2.jpg'],
            ['Household Cleaning', 'Deterjen Bubuk 800g', 'pcs', 16500, 21500, 40, 10, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTZmP0aLeVD_IKWn482vkDitLNlBhh8dAWsFTqfdpK_1g&s=10'],
            ['Canned & Jarred Goods', 'Sarden Kaleng 425g', 'pcs', 16500, 22000, 32, 8, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTrDQq5aoa4vSlFR51mSTy4ACNWXtLvtzGm9qAkum4gNw&s=10'],
            ['Pasta & Rice', 'Beras Premium 5kg', 'pcs', 58500, 69000, 20, 6, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxcq9nNrCvabcWK4ubTBPysOkL93ST4a_jfT3CNcgmKg&s=10'],
            ['Pasta & Rice', 'Mie Telur 200g', 'pcs', 5000, 7500, 65, 18, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQk2ZIAQPzFDWWst_xY19F968LDoiWTGTZUJrRK4G1REg&s=10'],
            ['Cooking Oils & Vinegars', 'Minyak Goreng 1L', 'pcs', 14500, 18500, 48, 12, 'https://cdn.ralali.id/assets/img/Libraries/163773_bimoli-minyak-goreng-1-liter-b---_153835908635744267146.jpg'],
            ['Spices & Herbs', 'Garam Dapur 500g', 'pcs', 3500, 5500, 54, 12, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQN0XfmeIaP1X4YuGpB2ErNPouBieAqogn_Fx7Lx8jNA&s=10'],
            ['Health & Wellness', 'Madu Murni 250ml', 'pcs', 31000, 42000, 14, 5, 'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/102/MTA-158460644/br-m036969-04428_nusantara-madu-murni-250ml_full01-79455cd0.jpg'],
            ['Baby Products', 'Popok Bayi M 20pcs', 'pcs', 42000, 56000, 18, 6, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSvj3c5ewkP9OMmdXfQQwWNncJJxDtncqqSS6RGK1c77g&s=10'],
        ];

        foreach ($products as $index => [$categoryName, $name, $unit, $buyPrice, $sellPrice, $stock, $minStock, $imageUrl]) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['description' => "Produk kategori {$categoryName} untuk kebutuhan toko harian."]
            );

            $sku = 'PRD-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'unit' => $unit,
                    'image_url' => $imageUrl,
                    'buy_price' => $buyPrice,
                    'sell_price' => $sellPrice,
                    'stock_qty' => $stock,
                    'min_stock' => $minStock,
                    'status' => 'active',
                ]
            );
        }
    }
}

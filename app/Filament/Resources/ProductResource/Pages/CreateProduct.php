<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\AgeCategoryProduct;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected function afterCreate(): void
    {
        $data = $this->data; // forma ma’lumotlari
        $product = $this->record; // yaratilgan product

        if (isset($data['ageCategoryProducts']) && is_array($data['ageCategoryProducts'])) {
            foreach ($data['ageCategoryProducts'] as $ageCategoryId => $values) {
                if (!empty($values['quantity'])) {
                    AgeCategoryProduct::create([
                        'product_id' => $product->id,
                        'age_category_id' => $ageCategoryId,
                        'quantity' => $values['quantity'],
                    ]);
                }
            }
        }
    }

}

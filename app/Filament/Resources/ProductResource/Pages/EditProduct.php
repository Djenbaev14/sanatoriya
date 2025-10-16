<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\AgeCategoryProduct;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // mavjud age_category_products dan quantity qiymatlarini yuklaymiz
        $product = $this->record;

        $data['ageCategoryProducts'] = [];

        foreach ($product->ageCategoryProducts as $item) {
            $data['ageCategoryProducts'][$item->age_category_id]['quantity'] = $item->quantity;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->data;
        $product = $this->record;

        if (isset($data['ageCategoryProducts']) && is_array($data['ageCategoryProducts'])) {
            foreach ($data['ageCategoryProducts'] as $ageCategoryId => $values) {
                if (!empty($values['quantity'])) {
                    AgeCategoryProduct::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'age_category_id' => $ageCategoryId,
                        ],
                        [
                            'quantity' => $values['quantity'],
                        ]
                    );
                } else {
                    // agar quantity bo‘sh bo‘lsa — o‘chiramiz
                    AgeCategoryProduct::where('product_id', $product->id)
                        ->where('age_category_id', $ageCategoryId)
                        ->delete();
                }
            }
        }
    } 
}

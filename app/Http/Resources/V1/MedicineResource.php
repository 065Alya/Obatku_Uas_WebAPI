<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->medicine_name,
            'generic_name'  => $this->generic_name,
            'category'      => $this->whenLoaded('category', fn() => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'owner_type'    => class_basename($this->owner_type),
            'owner_id'      => $this->owner_id,
            'dosage'        => $this->dosage,
            'unit'          => $this->unit,
            'form'          => $this->form,
            'manufacturer'  => $this->manufacturer,
            'description'   => $this->description,
            'side_effects'  => $this->side_effects,
            'stock'         => $this->stock,
            'stock_alert'   => $this->stock_alert,
            'is_low_stock'  => $this->isLowStock(),
            'price'         => $this->price,
            'expiry_date'   => $this->expiry_date?->toDateString(),
            'is_expired'    => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(30),
            'is_active'     => $this->is_active,
            'image'         => $this->image,
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }
}

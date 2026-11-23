<?php

namespace App\Services\CutBoard;

use App\Models\CutBoard\CutBoard;
use App\Models\Master\ProductTypeEmployee;
use App\Models\Master\WipWorking;

final class SourceSpecResolver
{
    public function resolve(TagCode $code, array $data): array
    {
        $row = CutBoard::withoutGlobalScopes()
            ->newQuery()
            ->where('sku_code', $code->skuCode)
            ->where('brd_lot', $code->lot)
            ->where('line', (int) $code->line)
            ->first([
                'brd_id', 'brd_working_id', 'bl_code', 'sku_code', 'brd_lot',
                'pe_type_name', 'ww_line', 'line', 'lot_detail',
            ]);

        $sourceId = trim((string) ($data['source_id'] ?? ''));
        $sourceType = strtoupper(trim((string) ($data['source_type'] ?? 'WIP')));
        $wip = null;
        $productTypeName = null;

        if ($sourceType === 'WIP' && $sourceId !== '' && ctype_digit($sourceId)) {
            $wip = WipWorking::query()->find((int) $sourceId);
            $productTypeName = $wip
                ? ProductTypeEmployee::query()->where('pe_working_id', $wip->ww_id)->value('pe_type_name')
                : null;
        }

        $brandCode = trim((string) ($data['brand_code'] ?? $row?->bl_code ?? $code->brandCode()));
        $productTypeCode = trim((string) ($data['product_type_code'] ?? $code->productTypeCode()));
        $masterProductName = $productTypeCode !== ''
            ? ProductTypeEmployee::query()->where('pe_type_code', $productTypeCode)->orderByDesc('pe_id')->value('pe_type_name')
            : null;
        $specCode = trim((string) ($data['spec_code'] ?? $productTypeCode));
        $specName = trim((string) ($data['spec_name'] ?? $row?->pe_type_name ?? $productTypeName ?? $masterProductName ?? ''));
        $skuCode = trim((string) ($data['sku_code'] ?? $row?->sku_code ?? $code->skuCode));
        $lotNo = trim((string) ($data['lot_no'] ?? $row?->brd_lot ?? $code->lot));
        $qty = $data['qty'] ?? $code->qty;

        return [
            'source_type' => $sourceType,
            'source_id' => $sourceId !== '' ? $sourceId : $skuCode.'|'.$lotNo,
            'wip_code' => $data['wip_code'] ?? ($sourceType === 'WIP' ? $sourceId : null),
            'fg_code' => $data['fg_code'] ?? ($sourceType === 'FG' ? $sourceId : null),
            'spec_code' => $specCode,
            'spec_name' => $specName !== '' ? $specName : null,
            'sku_code' => $skuCode,
            'lot_no' => $lotNo,
            'qty' => (float) $qty,
            'brand_code' => $brandCode,
            'product_type_code' => $productTypeCode,
            'legacy_brand_id' => $row?->brd_id,
            'legacy_working_id' => $row?->brd_working_id ?? $wip?->ww_id,
            'spec_snapshot' => array_filter([
                'source_type' => $sourceType,
                'brand_code' => $brandCode,
                'product_type_code' => $productTypeCode,
                'product_type_name' => $specName !== '' ? $specName : null,
                'sku_code' => $skuCode,
                'lot_no' => $lotNo,
                'line' => $code->line,
                'source_line_digit' => $sourceType === 'WIP' ? $code->line : null,
                'legacy_brand_id' => $row?->brd_id,
                'legacy_working_id' => $row?->brd_working_id ?? $wip?->ww_id,
            ], static fn ($value) => $value !== null && $value !== ''),
        ];
    }
}

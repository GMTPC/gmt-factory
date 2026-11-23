<?php

namespace App\Services\CutBoard;

use InvalidArgumentException;

final readonly class TagCode
{
    public function __construct(
        public string $skuCode,
        public string $lot,
        public string $qty,
        public string $line,
        public string $raw,
        public string $format = 'structured',
    ) {}

    public static function parse(string $raw): self
    {
        $barcode = strtoupper(trim($raw));

        // Legacy WIP barcode: W/B + source line + brand + '-' + product spec + date + shift + sequence + qty.
        // B codes are retained/holding pallets and the old L3 workflow accepts B4 as well as W4.
        // The 3-char sequence may contain letters on B codes (e.g. B499-A40109261104B·B03·077 = 77 sheets).
        if (preg_match('/^[WB]([1-4])(\d{2})-([A-Z0-9]{6})(\d{6})([A-Z])([A-Z0-9]{3})(\d{3})$/', $barcode, $matches) === 1) {
            return new self(
                skuCode: substr($barcode, 0, 11),
                lot: substr($barcode, 11, 10),
                qty: substr($barcode, -3),
                line: $matches[1],
                raw: $raw,
                format: 'legacy_wip',
            );
        }

        $values = ['skuCode' => '', 'lot' => '', 'qty' => '', 'line' => ''];

        foreach (explode('.', trim($raw)) as $part) {
            [$key, $value] = array_pad(explode(':', $part, 2), 2, null);

            match ($key) {
                'SKUCODE' => $values['skuCode'] = trim((string) $value),
                'LOT' => $values['lot'] = trim((string) $value),
                'QTY' => $values['qty'] = trim((string) $value),
                'L' => $values['line'] = trim((string) $value),
                default => null,
            };
        }

        if ($values['skuCode'] === '' || $values['lot'] === '' || $values['line'] === '') {
            throw new InvalidArgumentException('รูปแบบ QR ไม่ครบ: ต้องมี SKUCODE, LOT และ L');
        }

        return new self(...$values, raw: $raw);
    }

    public function interfaceQr(string $brandCode, string $productTypeCode, ?string $lot = null, ?string $qty = null): string
    {
        return 'B'.$this->line.$brandCode.'-'.$productTypeCode.($lot ?? $this->lot).str_pad((string) ($qty ?? $this->qty), 3, '0', STR_PAD_LEFT);
    }

    public function isBx(): bool
    {
        return str_starts_with(strtoupper($this->skuCode), 'BX');
    }

    public function brandCode(): string
    {
        $sku = strtoupper($this->skuCode);
        $dash = strpos($sku, '-');

        return $dash === false ? $sku : substr($sku, 2, $dash - 2);
    }

    public function productTypeCode(): string
    {
        $sku = strtoupper($this->skuCode);
        $dash = strpos($sku, '-');

        return $dash === false ? '' : substr($sku, $dash + 1);
    }

    public function lotDetail(string $brandCode, string $productTypeCode): string
    {
        return 'BX'.$brandCode.'-'.$productTypeCode.substr($this->lot, 0, 6).$this->line;
    }

    public function tagDate(string $brandCode, string $productTypeCode): string
    {
        return $this->lotDetail($brandCode, $productTypeCode).'0010000000';
    }

    public function tag(string $brandCode, string $productTypeCode): string
    {
        $sequence = (int) substr($this->lot, -3);

        return $this->lotDetail($brandCode, $productTypeCode).'001'.str_pad((string) $sequence, 7, '0', STR_PAD_LEFT);
    }
}

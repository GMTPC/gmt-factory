<?php

namespace Tests\Unit\CutBoard;

use App\Services\CutBoard\TagCode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TagCodeTest extends TestCase
{
    public function test_it_parses_the_qc_qr_format(): void
    {
        $code = TagCode::parse('SKUCODE:BR01-000001.LOT:240601B001.QTY:100.L:3');

        $this->assertSame('BR01-000001', $code->skuCode);
        $this->assertSame('240601B001', $code->lot);
        $this->assertSame('100', $code->qty);
        $this->assertSame('3', $code->line);
    }

    public function test_it_builds_the_view_compatible_codes_for_a_three_digit_quantity(): void
    {
        $code = TagCode::parse('SKUCODE:BR01-000001.LOT:240601B001.QTY:100.L:3');

        $this->assertSame('B3BR01-000001240601B001100', $code->interfaceQr('BR01', '000001'));
        $this->assertSame('BXBR01-0000012406013'.'0010000000', $code->tagDate('BR01', '000001'));
        $this->assertSame('BXBR01-0000012406013'.'0010000001', $code->tag('BR01', '000001'));
    }

    public function test_it_pads_a_quantity_below_one_hundred_like_the_view(): void
    {
        $code = TagCode::parse('SKUCODE:BR01-000001.LOT:240601B019.QTY:61.L:98');

        $this->assertSame('B98BR01-000001240601B019061', $code->interfaceQr('BR01', '000001'));
    }

    #[DataProvider('legacy_wip_barcodes')]
    public function test_it_parses_legacy_w_and_b_wip_barcodes(string $barcode, string $sku, string $lot, string $qty, string $line): void
    {
        $code = TagCode::parse($barcode);

        $this->assertSame('legacy_wip', $code->format);
        $this->assertSame($sku, $code->skuCode);
        $this->assertSame($lot, $code->lot);
        $this->assertSame($qty, $code->qty);
        $this->assertSame($line, $code->line);
    }

    public static function legacy_wip_barcodes(): array
    {
        return [
            'W4 pallet' => ['W499-A40109261030A001100', 'W499-A40109', '261030A001', '100', '4'],
            'B4 pallet with lettered sequence' => ['B499-A40109261104BB03077', 'B499-A40109', '261104BB03', '077', '4'],
            'W3 pallet' => ['W399-A10109261105A659100', 'W399-A10109', '261105A659', '100', '3'],
        ];
    }

    public function test_it_rejects_a_qr_without_required_parts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TagCode::parse('SKUCODE:BR01-000001.QTY:100');
    }

    #[DataProvider('real_view_rows')]
    public function test_it_matches_the_legacy_view_for_real_rows(string $sku, string $lot, string $qty, string $line, string $brand, string $type, string $interfaceQr, string $tagDate, string $tag): void
    {
        $code = TagCode::parse("SKUCODE:$sku.LOT:$lot.QTY:$qty.L:$line");

        $this->assertSame($interfaceQr, $code->interfaceQr($brand, $type));
        $this->assertSame($tagDate, $code->tagDate($brand, $type));
        $this->assertSame($tag, $code->tag($brand, $type));
    }

    public static function real_view_rows(): array
    {
        return [
            ['BX98-A10109', '210803A019', '61', '1', '98', 'A10109', 'B198-A10109210803A019061', 'BX98-A1010921080310010000000', 'BX98-A1010921080310010000019'],
            ['BX33-A60113', '210809B001', '76', '3', '33', 'A60113', 'B333-A60113210809B001076', 'BX33-A6011321080930010000000', 'BX33-A6011321080930010000001'],
            ['BX33-A60113', '210809B002', '76', '3', '33', 'A60113', 'B333-A60113210809B002076', 'BX33-A6011321080930010000000', 'BX33-A6011321080930010000002'],
            ['BX33-A60113', '210809B003', '76', '3', '33', 'A60113', 'B333-A60113210809B003076', 'BX33-A6011321080930010000000', 'BX33-A6011321080930010000003'],
            ['BX33-A60113', '210809B004', '76', '3', '33', 'A60113', 'B333-A60113210809B004076', 'BX33-A6011321080930010000000', 'BX33-A6011321080930010000004'],
        ];
    }
}

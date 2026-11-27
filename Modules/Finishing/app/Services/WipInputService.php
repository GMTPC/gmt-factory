<?php

namespace Modules\Finishing\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * WIP tags received into a Finishing Job and how many sheets of each went into which output tag.
 *   remaining of a received tag = tag quantity - sheets already used by confirmed outputs.
 * An output may use several tags; a tag may be split over several outputs; output never exceeds what is left.
 */
class WipInputService
{
    public const TABLE = 'finishing_log_inputs';

    private const EPSILON = 0.0005;

    public static function ready(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    public static function assertReady(): void
    {
        abort_unless(self::ready(), 503, 'ระบบยังไม่ได้อัปเดตฐานข้อมูล (php artisan migrate) กรุณาแจ้งผู้ดูแลระบบ');
    }

    /** @return Collection<int, float> receipt_id => sheets used */
    public function usedByReceipt(iterable $receiptIds): Collection
    {
        $ids = collect($receiptIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty() || ! self::ready()) {
            return collect();
        }

        return DB::table(self::TABLE)->whereIn('receipt_id', $ids)->groupBy('receipt_id')
            ->selectRaw('receipt_id, SUM(quantity) as used')->pluck('used', 'receipt_id')
            ->mapWithKeys(fn ($used, $id) => [(int) $id => (float) $used]);
    }

    /** Received tags of a Job with tag quantity, used and remaining (oldest first). */
    public function receipts(int $jobId): Collection
    {
        $receipts = DB::table('finishing_wip_receipts')->where('job_id', $jobId)->orderBy('id')
            ->get(['id', 'scan_code', 'quantity', 'production_log_id', 'created_at']);
        $used = self::ready() ? $this->usedByReceipt($receipts->pluck('id')) : collect();

        return $receipts->map(function ($receipt) use ($used) {
            $tag = (float) $receipt->quantity;
            // Before the migration a receipt was either fully used (linked to a log) or not used at all.
            $usedQty = self::ready() ? (float) ($used[(int) $receipt->id] ?? 0) : ($receipt->production_log_id ? $tag : 0.0);
            $receipt->tag_quantity = $tag;
            $receipt->used = round($usedQty, 3);
            $receipt->remaining = round(max(0, $tag - $usedQty), 3);

            return $receipt;
        });
    }

    /**
     * Input tags of each log.
     *
     * @return Collection<int, list<array{receipt_id: int, scan_code: string, quantity: float}>> log_id => inputs
     */
    public function inputsByLog(iterable $logIds): Collection
    {
        $ids = collect($logIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }
        if (! self::ready()) {
            return DB::table('finishing_wip_receipts')->whereIn('production_log_id', $ids)->orderBy('id')
                ->get(['id', 'scan_code', 'quantity', 'production_log_id'])
                ->groupBy('production_log_id')
                ->map(fn ($rows) => $rows->map(fn ($row) => ['receipt_id' => (int) $row->id, 'scan_code' => $row->scan_code, 'quantity' => (float) $row->quantity])->values()->all());
        }

        return DB::table(self::TABLE)->whereIn('production_log_id', $ids)->orderBy('id')
            ->get(['receipt_id', 'scan_code', 'quantity', 'production_log_id'])
            ->groupBy('production_log_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => ['receipt_id' => (int) $row->receipt_id, 'scan_code' => $row->scan_code, 'quantity' => (float) $row->quantity])->values()->all());
    }

    /**
     * Take $quantity sheets from the receipts in the given order (each up to what it has left).
     * Every receipt must contribute: a scanned tag that ends up unused is a mistake, not a silent no-op.
     *
     * @param  list<object>  $receipts  rows of finishing_wip_receipts (id, scan_code, quantity), locked by the caller
     */
    public function allocate(int $logId, array $receipts, float $quantity): void
    {
        $used = $this->usedByReceipt(array_map(fn ($receipt) => $receipt->id, $receipts));
        $left = $quantity;
        $now = now();
        foreach (array_values($receipts) as $index => $receipt) {
            $remaining = (float) $receipt->quantity - (float) ($used[(int) $receipt->id] ?? 0);
            if ($remaining <= self::EPSILON) {
                throw ValidationException::withMessages(['inputs' => 'Tag '.$receipt->scan_code.' แปรรูปครบจำนวนแล้ว']);
            }
            if ($left <= self::EPSILON) {
                throw ValidationException::withMessages(['quantity_out' => 'จำนวน WIP ออก '.$this->format($quantity).' ใช้ไม่ถึง Tag ที่ '.($index + 1).' ('.$receipt->scan_code.') · เอา Tag นี้ออก หรือเพิ่มจำนวน WIP ออก']);
            }
            $take = min($remaining, $left);
            DB::table(self::TABLE)->insert([
                'production_log_id' => $logId,
                'receipt_id' => $receipt->id,
                'scan_code' => $receipt->scan_code,
                'quantity' => round($take, 3),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $left -= $take;
        }
        if ($left > self::EPSILON) {
            throw ValidationException::withMessages(['quantity_out' => 'จำนวน WIP ออก '.$this->format($quantity).' เกินยอด WIP รับเข้า (ใช้ได้สูงสุด '.$this->format($quantity - $left).')']);
        }
    }

    /**
     * Change how many sheets an existing output uses: more is taken from its own input tags only (in order),
     * less is given back starting from the last input tag.
     */
    public function resize(int $logId, float $from, float $to): void
    {
        $inputs = DB::table(self::TABLE)->where('production_log_id', $logId)->orderBy('id')->lockForUpdate()->get();
        if ($inputs->isEmpty()) {
            throw ValidationException::withMessages(['quantity_out' => 'รายการนี้ไม่มีข้อมูล Tag WIP รับเข้า จึงแก้จำนวนไม่ได้']);
        }
        $delta = $to - $from;
        if ($delta > self::EPSILON) {
            $receipts = DB::table('finishing_wip_receipts')->whereIn('id', $inputs->pluck('receipt_id'))->lockForUpdate()->get(['id', 'quantity'])->keyBy('id');
            $used = $this->usedByReceipt($inputs->pluck('receipt_id'));
            $left = $delta;
            foreach ($inputs as $input) {
                $room = (float) ($receipts[$input->receipt_id]->quantity ?? 0) - (float) ($used[(int) $input->receipt_id] ?? 0);
                if ($room <= self::EPSILON || $left <= self::EPSILON) {
                    continue;
                }
                $take = min($room, $left);
                DB::table(self::TABLE)->where('id', $input->id)->update(['quantity' => round((float) $input->quantity + $take, 3), 'updated_at' => now()]);
                $used[(int) $input->receipt_id] = (float) ($used[(int) $input->receipt_id] ?? 0) + $take;
                $left -= $take;
            }
            if ($left > self::EPSILON) {
                throw ValidationException::withMessages(['quantity_out' => 'จำนวน WIP ออกเกินยอด WIP รับเข้าของรายการนี้ (ได้สูงสุด '.$this->format($to - $left).')']);
            }
        } elseif ($delta < -self::EPSILON) {
            $left = -$delta;
            foreach ($inputs->reverse() as $input) {
                if ($left <= self::EPSILON) {
                    break;
                }
                $take = min((float) $input->quantity, $left);
                $rest = round((float) $input->quantity - $take, 3);
                $rest <= self::EPSILON
                    ? DB::table(self::TABLE)->where('id', $input->id)->delete()
                    : DB::table(self::TABLE)->where('id', $input->id)->update(['quantity' => $rest, 'updated_at' => now()]);
                $left -= $take;
            }
        }
    }

    /** Cancelled output: its input sheets become available again. */
    public function release(int $logId): void
    {
        if (self::ready()) {
            DB::table(self::TABLE)->where('production_log_id', $logId)->delete();
        }
        DB::table('finishing_wip_receipts')->where('production_log_id', $logId)->update(['production_log_id' => null, 'updated_at' => now()]);
    }

    /** Largest quantity an output may be changed to: what it uses now + what its input tags still have left. */
    public function maxQuantity(float $current, array $inputs, Collection $receiptsById): float
    {
        $extra = collect($inputs)->pluck('receipt_id')->unique()
            ->sum(fn ($id) => (float) ($receiptsById[$id]->remaining ?? 0));

        return round($current + $extra, 3);
    }

    private function format(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ','), '0'), '.');
    }
}

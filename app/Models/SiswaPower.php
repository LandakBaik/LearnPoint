<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SiswaPower kini berperan sebagai adapter ke tabel 'inventories' khusus item berkategori Power.
 * Menjaga kompatibilitas 100% terhadap controller dan logic lama.
 */
class SiswaPower extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'siswa_id',
        'item_id',
        'jumlah',
        'is_equipped',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'      => 'integer',
            'is_equipped' => 'boolean',
            'metadata'    => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('powerOnly', function (Builder $builder) {
            $builder->whereHas('item', function ($q) {
                $q->where('kategori', 'power');
            });
        });

        static::creating(function ($model) {
            if (empty($model->item_id) && !empty($model->power_id)) {
                $model->item_id = $model->power_id;
            }
        });
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function power()
    {
        return $this->belongsTo(Power::class, 'item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function getPowerIdAttribute(): ?int
    {
        return $this->attributes['item_id'] ?? null;
    }

    public function setPowerIdAttribute($value): void
    {
        $this->attributes['item_id'] = $value;
    }

    public static function initializeDefaultInventory(Siswa $siswa): void
    {
        Inventory::initializeDefaultInventory($siswa);
    }
}

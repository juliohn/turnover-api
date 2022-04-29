<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Balance extends Model
{
    use SoftDeletes;
    protected $table = 'balances';

    protected $appends = array('amount_show','created_show','status_show','check_url');
    protected $with = ['account'];

    public function getStatusShowAttribute()
    {
        return status_format($this->attributes['status']);
    }

    public function getAmountShowAttribute()
    {
        return currency_format($this->attributes['amount']);
    }

    public function getCreatedShowAttribute()
    {
        return date('m/d/Y', strtotime($this->attributes['created_at'])). ' at '. date('H:i:s', strtotime($this->attributes['created_at']));
    }

    public function getCheckUrlAttribute()
    {
        if ($this->attributes['image_path'] !== null) {
            return str_replace('public/', 'storage/', asset($this->attributes['image_path']));
        }
        return $this->attributes['image_path'];
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account', 'account_id', 'id');
    }
}

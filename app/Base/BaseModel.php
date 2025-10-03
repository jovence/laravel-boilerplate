<?php

namespace App\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuidAndFilters;

class BaseModel extends Model
{
    use SoftDeletes, HasUuidAndFilters;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;
}

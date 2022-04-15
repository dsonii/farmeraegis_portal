<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuantityFinder extends Model
{

    protected $table = 'quantity_finder';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the unit associated with the purchase line.
     */
    public function quantiy()
    {
        return $this->hasMany(\App\QuantityFinderMapping::class, 'refrence_id');
    }
}

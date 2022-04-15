<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuantityFinderMapping extends Model
{
    protected $table = 'quantity_finder_mapping';

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
        return $this->hasMany(\App\QuantityFinder::class, 'refrence_id');
    }
}

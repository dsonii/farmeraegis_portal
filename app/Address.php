<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Util;
use DB;

/**
 * Class Address
 * @package App\Models
 * @version September 4, 2019, 3:38 pm UTC
 *
 * @property \App\Models\Food food
 * @property \App\Models\User user
 * @property \Illuminate\Database\Eloquent\Collection extra
 * @property integer food_id
 * @property integer user_id
 * @property integer quantity
 */
class Address extends Model
{

    public $table = 'addresses';
    


    public $fillable = [
        'user_id',
        'address_type',
        'name',
        'first_line',
        'second_line',
        'mobile',
        'alt_mobile',
        'city',
        'state',
        'country',
        'pincode',
        'near_by',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id'       => 'integer',
        'name'          => 'string',
        'first_line'    => 'string',
        'second_line'   => 'string',
        'mobile'        => 'string',
        'alt_mobile'    => 'string',
        'city'          => 'string',
        'state'         => 'string',
        'country'       => 'string',
        'pincode'       => 'integer',
        'near_by'       => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id'       => 'required|exists:users,id',
        'name'          => 'required',
        'first_line'    => 'required',
        'mobile'        => 'required',
        'city'          => 'required',
        'state'         => 'required',
        'country'       => 'required',
        'pincode'       => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id')->where("address_type","user");
    }
}
<?php


namespace Thomas\Bundle\Models;


use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['myanmar', 'chinese', 'type_for'];

    protected $hidden = ['languageable_type', 'languageable_id', 'created_at', 'updated_at'];

    public function languageable()
    {
        return $this->morphTo();
    }
}

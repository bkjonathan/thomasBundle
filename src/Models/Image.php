<?php
namespace Thomas\Bundle\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable=['url','type'];

    protected $hidden=['imageable_type','imageable_id','created_at','updated_at'];
    public function imageable()
    {
        return $this->morphTo();
    }
}

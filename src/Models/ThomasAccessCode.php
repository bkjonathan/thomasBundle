<?php


namespace Thomas\Bundle\Models;


use Illuminate\Database\Eloquent\Model;

class ThomasAccessCode extends Model
{
    protected $guarded=[];

    public function devices(){
        return $this->hasMany(ThomasAccessCodeDevice::class);
    }
}

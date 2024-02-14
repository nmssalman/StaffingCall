<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = "staffing_groups";
    
    public function groupManagerInfo() {
     return $this->hasMany('App\User','businessGroupID');
    }
    
}

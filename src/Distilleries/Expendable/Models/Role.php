<?php namespace Distilleries\Expendable\Models;

class Role extends BaseModel {

    protected $fillable = [
        'libelle',
        'initials',
        'overide_permission'
    ];

    public function user()
    {
        return $this->hasOne('Distilleries\Expendable\Models\User');
    }

    public function permissions()
    {
        return $this->hasMany('Distilleries\Expendable\Models\Permission');
    }
}
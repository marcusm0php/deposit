<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class ModelBase extends ModelBase
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = '';
    protected $dateFormat = 'Y-m-d H:i:s';
    
    public function getUpdatedAtColumn() {
        parent::getUpdatedAtColumn();
    }
}
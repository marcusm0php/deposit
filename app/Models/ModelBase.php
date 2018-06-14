<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelBase extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $dateFormat = 'Y-m-d H:i:s';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->init();
    }

    public function init()
    {
        $this->hidden[] = $this->primaryKey;
    }
}
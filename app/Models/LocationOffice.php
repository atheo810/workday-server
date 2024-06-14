<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationOffice extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'location_offices';
    // Define the fill able columns
    protected $guarded = [
        'created_at',
        'updated_at',
    ];
}

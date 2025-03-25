<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;
    // app/Models/CompanyProfile.php
    protected $fillable = ['title', 'content', 'image_path'];
}

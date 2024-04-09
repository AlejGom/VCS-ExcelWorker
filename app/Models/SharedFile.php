<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    use HasFactory;

    protected $table = 'shared_files';
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'id_file');
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'photo'];

    protected $hidden = ['password', 'remember_token'];

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . ltrim(str_replace('storage/', '', $this->photo), '/'))
            : asset('admin/assets/img/profile.jpg');
    }
} 
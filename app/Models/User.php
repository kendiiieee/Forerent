<?php

namespace App\Models;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'email',
        'contact',
        'password',
        'profile_img',
        'role',
        'permanent_address',
        'government_id_type',
        'government_id_number',
        'government_id_image',
        'company_school',
        'position_course',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // If user is a landlord
    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id', 'user_id');
    }

    // If user is a manager
    public function managedUnits()
    {
        return $this->hasMany(Unit::class, 'manager_id', 'user_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'manager_id', 'user_id');
    }

    // If user is a tenant
    public function leases()
    {
        return $this->hasMany(Lease::class, 'tenant_id', 'user_id');
    }

    // Announcements authored by user
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'author_id', 'user_id');
    }

    public function unitsManaged()
    {
        return $this->hasMany(Unit::class, 'manager_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));

        return $name !== '' ? $name : ((string) ($this->email ?? 'User'));
    }

    public function getProfileImageUrlAttribute(): string
    {
        if (!empty($this->profile_img)) {
            $path = trim((string) $this->profile_img);

            if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }

            $path = ltrim($path, '/');

            if (Str::startsWith($path, 'storage/')) {
                $path = Str::after($path, 'storage/');
            }

            if (Storage::disk('public')->exists($path)) {
                return Storage::url($path);
            }
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->display_name) . '&background=C8D9FD&color=0C0B50';
    }
}

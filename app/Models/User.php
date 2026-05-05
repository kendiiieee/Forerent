<?php

namespace App\Models;

use App\Models\Concerns\HasPsgcAddress;
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
    use HasFactory, Notifiable, SoftDeletes, HasPsgcAddress;

    protected static array $psgcAddressMap = [
        'permanent' => ['permanent_address', 'permanent_'],
    ];

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
        'permanent_province_id',
        'permanent_city_id',
        'permanent_barangay_id',
        'permanent_street',
        'government_id_type',
        'government_id_number',
        'government_id_image',
        'company_school',
        'position_course',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
        'terms_accepted_at',
        'rental_eligibility',
        'eligibility_notes',
        'eligibility_changed_at',
        'eligibility_changed_by',
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
            'terms_accepted_at' => 'datetime',
            'eligibility_changed_at' => 'datetime',
        ];
    }

    public function isBlockedFromRenting(): bool
    {
        return $this->rental_eligibility === 'blocked';
    }

    public function block(string $reason, ?int $leaseId = null, ?int $actorId = null): void
    {
        if ($this->rental_eligibility === 'blocked') {
            return;
        }

        $oldStatus = $this->rental_eligibility ?? 'eligible';

        $this->forceFill([
            'rental_eligibility'    => 'blocked',
            'eligibility_notes'     => $reason,
            'eligibility_changed_at' => now(),
            'eligibility_changed_by' => $actorId,
        ])->save();

        TenantEligibilityLog::create([
            'user_id'    => $this->user_id,
            'changed_by' => $actorId,
            'lease_id'   => $leaseId,
            'old_status' => $oldStatus,
            'new_status' => 'blocked',
            'reason'     => $reason,
            'created_at' => now(),
        ]);
    }

    public function reinstate(string $reason, User $actor): void
    {
        if ($actor->role !== 'landlord') {
            throw new \RuntimeException('Only the landlord can reinstate a blocked tenant.');
        }

        if ($this->rental_eligibility !== 'blocked') {
            return;
        }

        $this->forceFill([
            'rental_eligibility'     => 'eligible',
            'eligibility_notes'      => $reason,
            'eligibility_changed_at' => now(),
            'eligibility_changed_by' => $actor->user_id,
        ])->save();

        TenantEligibilityLog::create([
            'user_id'    => $this->user_id,
            'changed_by' => $actor->user_id,
            'lease_id'   => null,
            'old_status' => 'blocked',
            'new_status' => 'eligible',
            'reason'     => $reason,
            'created_at' => now(),
        ]);
    }

    public function appendEligibilityNote(string $note): void
    {
        $existing = trim((string) ($this->eligibility_notes ?? ''));
        $timestamped = '[' . now()->format('Y-m-d') . '] ' . $note;
        $this->forceFill([
            'eligibility_notes' => $existing === '' ? $timestamped : $existing . "\n" . $timestamped,
        ])->save();
    }

    public function eligibilityLogs()
    {
        return $this->hasMany(TenantEligibilityLog::class, 'user_id', 'user_id')->latest('created_at');
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

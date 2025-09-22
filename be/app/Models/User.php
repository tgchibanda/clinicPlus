<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'provider_id',
        'provider',
        'access_token'
    ];

    protected $guarded = ['*'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Override the mail body for reset password notification mail.
     */
    // public function sendPasswordResetNotification($token)
    // {
    //     $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
    // }

    public function qualification()
    {
        return $this->belongsToMany(Qualification::class)
            ->withTimeStamps();
    }
    public function special_area()
    {
        return $this->belongsToMany(SpecialArea::class)
            ->withTimeStamps();
    }
    public function contact()
    {
        return $this->belongsToMany(Contact::class)
            ->withTimeStamps();
    }
    public function upload()
    {
        return $this->belongsToMany(Upload::class)
            ->withTimeStamps();
    }
    // app/Models/User.php
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }
}

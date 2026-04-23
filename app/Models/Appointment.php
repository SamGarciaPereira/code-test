<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'vet_id',
        'date',
        'time',
        'observations',
        'is_finished'
    ];

	public function patient(){
         return $this->belongsTo(Patient::class); 
    }
	public function vet() {
         return $this->belongsTo(User::class, 'vet_id'); 
    }
}

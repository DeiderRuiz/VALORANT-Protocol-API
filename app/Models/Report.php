<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'description',
        'photo',
        'agent_id',
        'mission_id',
        'related_report_id'
    ];

    // Misión en la que se hizo el reporte
    public function mission(){
        return $this->belongsTo(Mission::class);
    }

    // Para Relación auto-referenciada
    // Primer reporte
    public function parent(){
        return $this->belongsTo(Report::class, 'related_event_id');
    }
    // Respuesta el primer reporte
    public function responses(){
        return $this->hasMany(Report::class, 'related_event_id');
    }
}

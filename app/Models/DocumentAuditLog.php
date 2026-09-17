<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAuditLog extends Model
{
    use HasFactory;

    protected $table = 'document_audit_logs';

    protected $fillable = [
        'document_type',
        'document_id',
        'user_id',
        'action',
        'previous_status',
        'new_status',
        'version',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function getKeteranganAttribute(): ?string
    {
        return $this->notes;
    }

    public static function record(
        string $documentType,
        int $documentId,
        string $action,
        ?string $previousStatus,
        string $newStatus,
        int $version = 1,
        ?string $notes = null
    ): self {
        $userId = auth()->id();
        $ip = request()->ip();

        // Catat juga ke log aktivitas global sistem
        $actionTitle = strtoupper($documentType) . ' - ' . ucfirst(str_replace('_', ' ', $action));
        $desc = "Dokumen ID #{$documentId} status diubah dari [{$previousStatus}] ke [{$newStatus}] (Versi {$version}). " . ($notes ? "Catatan: {$notes}" : "");
        LogAktivitas::catat($actionTitle, $desc);

        return self::create([
            'document_type' => $documentType,
            'document_id' => $documentId,
            'user_id' => $userId,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'version' => $version,
            'notes' => $notes,
            'ip_address' => $ip,
        ]);
    }
}

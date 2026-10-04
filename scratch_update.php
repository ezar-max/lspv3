<?php
$file = 'c:/laragon/www/lspv3/resources/views/asesi/tahapan/index.blade.php';
$content = file_get_contents($file);

// 1. Tambahkan $isReadOnlyApl01
$content = preg_replace(
    '/(<div x-show="currentStep === 1".*?>)/',
    "$1\n            @php \$isReadOnlyApl01 = \$isAccAdmin || \$isDiajukan; @endphp",
    $content
);

// 2. Ganti semua penggunaan $isAccAdmin di dalam blok Step 1 (sampai blok Step 2)
$parts = explode('STEP 2: FORMULIR FR.APL.02', $content);
if(count($parts) == 2) {
    $step1 = $parts[0];
    $step2 = $parts[1];

    $step1 = str_replace('$isAccAdmin ?', '$isReadOnlyApl01 ?', $step1);
    
    // Replace the specific @if($isAccAdmin) for the bottom action buttons
    // Wait, there are multiple @if($isAccAdmin) in Step 1.
    // 1. For read-only badge in Section 1, 2, 3, etc.
    // Let's just replace all `@if($isAccAdmin)` with `@if($isReadOnlyApl01)` in Step 1.
    $step1 = str_replace('@if($isAccAdmin)', '@if($isReadOnlyApl01)', $step1);
    
    // Revert the read-only badge text to be dynamic if needed, or leave it. 
    // It's fine if the badge says "Terkunci (Read-Only)".

    // Ganti teks untuk bottom action buttons:
    $oldBtnText = '<span>Data FR.APL.01 telah dikunci (Read-Only) karena telah disetujui oleh Admin LSP.</span>';
    $newBtnText = '<span>Data FR.APL.01 telah dikunci (Read-Only) karena {{ $isAccAdmin ? \'telah disetujui oleh Admin LSP\' : \'sedang menunggu verifikasi Admin LSP\' }}.</span>';
    $step1 = str_replace($oldBtnText, $newBtnText, $step1);

    $content = $step1 . 'STEP 2: FORMULIR FR.APL.02' . $step2;
    file_put_contents($file, $content);
    echo "Done replacing step 1 read only state.\n";
} else {
    echo "Could not split at STEP 2.\n";
}

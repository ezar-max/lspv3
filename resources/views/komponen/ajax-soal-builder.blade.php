<script>
document.addEventListener('DOMContentLoaded', function() {
    const pgForm = document.getElementById('form-tambah-soal-pg');
    const esaiForm = document.getElementById('form-tambah-soal-esai');

    async function handleAjaxSubmit(e, form) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const result = await response.json();
                
                // Show success notification (could use Toastr/SweetAlert if available)
                // alert('Berhasil: ' + result.message);
                
                // Preserve scroll position
                sessionStorage.setItem('scrollPosition', window.scrollY);
                
                // Reload to show the new question in the list
                window.location.reload();
            } else {
                alert('Gagal menyimpan soal. Pastikan semua field terisi dengan benar.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    if (pgForm) {
        pgForm.addEventListener('submit', function(e) { handleAjaxSubmit(e, this); });
    }
    
    if (esaiForm) {
        esaiForm.addEventListener('submit', function(e) { handleAjaxSubmit(e, this); });
    }

    // Restore scroll position after reload
    const savedScroll = sessionStorage.getItem('scrollPosition');
    if (savedScroll) {
        window.scrollTo(0, parseInt(savedScroll));
        sessionStorage.removeItem('scrollPosition');
    }
});
</script>

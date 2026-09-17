/* PENDAFTARAN-BAGIAN2.JS - Logic dynamic units table & option toggle */

document.addEventListener('DOMContentLoaded', () => {
  const radioTujuanList = document.querySelectorAll('input[name="tujuan_asesmen"]');
  const boxTujuanLainnya = document.getElementById('box-tujuan-lainnya');

  radioTujuanList.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'Lainnya') {
        if (boxTujuanLainnya) boxTujuanLainnya.style.display = 'block';
      } else {
        if (boxTujuanLainnya) boxTujuanLainnya.style.display = 'none';
      }
    });
  });

  const selectSkema = document.getElementById('select-skema-apl01');
  if (selectSkema) {
    function updateTabelUnit() {
      const skemaId = selectSkema.value;
      document.querySelectorAll('.tabel-unit-skema').forEach(table => {
        if (table.getAttribute('data-skema-id') === skemaId) {
          table.style.display = 'table';
        } else {
          table.style.display = 'none';
        }
      });
    }

    selectSkema.addEventListener('change', updateTabelUnit);
    updateTabelUnit();
  }
});

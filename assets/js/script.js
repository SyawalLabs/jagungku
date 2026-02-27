// Tampilkan/sembunyikan custom date range
document.getElementById('exportPeriod').addEventListener('change', function() {
    const customRange = document.getElementById('customDateRange');
    customRange.style.display = this.value === 'custom' ? 'block' : 'none';
});

// Fungsi untuk export Excel
function exportToExcel() {
    document.getElementById('formatExcel').checked = true;
    const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
    exportModal.show();
    
    document.getElementById('confirmExport').onclick = function() {
        const dataType = document.getElementById('exportDataType').value;
        const period = document.getElementById('exportPeriod').value;
        
        let url = 'export_laporan.php?format=excel&type=' + dataType + '&period=' + period;
        
        // Tambahkan parameter periode saat ini
        url += '&tahun=<?= $tahun ?>&bulan=<?= $bulan ?>';
        
        if (period === 'custom') {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            url += '&start_date=' + startDate + '&end_date=' + endDate;
        }
        
        window.open(url, '_blank');
        exportModal.hide();
    };
}

// Fungsi untuk export PDF
function exportToPDF() {
    document.getElementById('formatPDF').checked = true;
    const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
    exportModal.show();
    
    document.getElementById('confirmExport').onclick = function() {
        const dataType = document.getElementById('exportDataType').value;
        const period = document.getElementById('exportPeriod').value;
        
        let url = 'export_laporan.php?format=pdf&type=' + dataType + '&period=' + period;
        
        // Tambahkan parameter periode saat ini
        url += '&tahun=<?= $tahun ?>&bulan=<?= $bulan ?>';
        
        if (period === 'custom') {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            url += '&start_date=' + startDate + '&end_date=' + endDate;
        }
        
        window.open(url, '_blank');
        exportModal.hide();
    };
}

// Fungsi untuk print sudah menggunakan window.print() bawaan
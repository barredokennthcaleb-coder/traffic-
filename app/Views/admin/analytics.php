<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Analytics<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Violations by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Violations by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Chart
    const statusData = <?= json_encode($status_counts) ?>;
    const statusLabels = statusData.map(item => item.status);
    const statusValues = statusData.map(item => item.count);

    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: [
                    '#ffc107', // Pending
                    '#198754', // Paid
                    '#dc3545'  // Cancelled
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Type Chart
    const typeData = <?= json_encode($type_counts) ?>;
    const typeLabels = typeData.map(item => item.violation_type);
    const typeValues = typeData.map(item => item.count);

    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: {
            labels: typeLabels,
            datasets: [{
                label: 'Number of Violations',
                data: typeValues,
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
<?= $this->endSection() ?>

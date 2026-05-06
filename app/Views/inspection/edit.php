<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Edit Inspection - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center mb-4">
                <a href="<?= base_url('inspections') ?>" class="btn btn-white border shadow-sm me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="mb-0 fw-bold">Edit Inspection Report</h4>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>General Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?= base_url('inspections/update/' . $inspection['id']) ?>" method="POST" id="inspectionForm">
                        <?= csrf_field() ?>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">MTOP # <span class="text-danger">*</span></label>
                                <input type="text" name="mtop_no" class="form-control" required value="<?= esc($inspection['mtop_no']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name of Operator <span class="text-danger">*</span></label>
                                <input type="text" name="operator_name" class="form-control" required value="<?= esc($inspection['operator_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cellphone # Operator</label>
                                <input type="text" name="operator_cellphone" class="form-control" value="<?= esc($inspection['operator_cellphone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cellphone # Driver</label>
                                <input type="text" name="driver_cellphone" class="form-control" value="<?= esc($inspection['driver_cellphone']) ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Make / Type of MV</label>
                                <input type="text" name="mv_make_type" class="form-control" value="<?= esc($inspection['mv_make_type']) ?>">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-4 fw-bold text-primary"><i class="bi bi-list-check me-2"></i>Inspection Checklist</h6>
                        
                        <div class="row g-3 mb-5">
                            <?php 
                            $checkboxes = [
                                'cert_attendance' => 'Cert. of Attendance of the Driver (CTRAMO Seminar)',
                                'outside_route' => 'Outside route',
                                'city_proper' => 'City Proper',
                                'trash_can' => 'Trash Can',
                                'mtop_8x16_route' => 'MTOP # 8 x 16 Su-ay/Ilog route (Fixed)',
                                'mtop_8x8_route' => 'MTOP # 8x8 City Proper/Outside Route (Fixed)',
                                'mtop_dashboard' => "MTOP # dash board/Name of operator's",
                                'signal_light_left' => 'Signal light/left',
                                'signal_light_right' => 'Signal light/right',
                                'head_light' => 'Head light',
                                'stop_light' => 'stop light',
                                'side_mirror' => 'Side Mirror left & right',
                                'horn' => 'Horn',
                                'standard_muffler' => 'Standard Muffler',
                                'color_coding' => 'Color coding',
                                'tarifa' => 'Tarifa'
                            ];
                            foreach ($checkboxes as $key => $label): 
                            ?>
                                <div class="col-md-6">
                                    <div class="form-check custom-checkbox py-2">
                                        <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="<?= $key ?>" <?= $inspection[$key] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?= $key ?>"><?= $label ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h6 class="border-bottom pb-2 mb-4 fw-bold text-primary"><i class="bi bi-person-check me-2"></i>Verification</h6>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Inspected by <span class="text-danger">*</span></label>
                                <input type="text" name="inspected_by" class="form-control" required value="<?= esc($inspection['inspected_by']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="inspection_date" class="form-control" required value="<?= esc($inspection['inspection_date']) ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= base_url('inspections') ?>" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">Update Inspection Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-white { background: #fff; }
    .custom-checkbox {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding-left: 2.5rem !important;
        transition: all 0.2s;
        cursor: pointer;
    }
    .custom-checkbox:hover {
        background: #eef2ff;
        border-color: #c7d2fe;
    }
    .custom-checkbox .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        margin-top: 0.15rem;
    }
</style>
<?= $this->endSection() ?>

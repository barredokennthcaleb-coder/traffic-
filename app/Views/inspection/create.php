<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>New Inspection - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center mb-4">
                <a href="<?= base_url('inspections') ?>" class="btn btn-white border shadow-sm me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="mb-0 fw-bold">Create Inspection Report</h4>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2"></i>General Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?= base_url('inspections/store') ?>" method="POST" id="inspectionForm">
                        <?= csrf_field() ?>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">MTOP # <span class="text-danger">*</span></label>
                                <input type="text" name="mtop_no" class="form-control" required placeholder="Enter MTOP Number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name of Operator <span class="text-danger">*</span></label>
                                <input type="text" name="operator_name" class="form-control" required placeholder="Enter Operator Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cellphone # Operator</label>
                                <input type="text" name="operator_cellphone" class="form-control" placeholder="09123456789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cellphone # Driver</label>
                                <input type="text" name="driver_cellphone" class="form-control" placeholder="09123456789">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Make / Type of MV</label>
                                <input type="text" name="mv_make_type" class="form-control" placeholder="e.g. Honda TMX / Tricycle">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-4 fw-bold text-primary"><i class="bi bi-list-check me-2"></i>Inspection Checklist</h6>
                        
                        <div class="row g-3 mb-5">
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="cert_attendance" id="cert_attendance">
                                    <label class="form-check-label" for="cert_attendance">Cert. of Attendance of the Driver (CTRAMO Seminar)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="outside_route" id="outside_route">
                                    <label class="form-check-label" for="outside_route">Outside route</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="city_proper" id="city_proper">
                                    <label class="form-check-label" for="city_proper">City Proper</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="trash_can" id="trash_can">
                                    <label class="form-check-label" for="trash_can">Trash Can</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="mtop_8x16_route" id="mtop_8x16_route">
                                    <label class="form-check-label" for="mtop_8x16_route">MTOP # 8 x 16 Su-ay/Ilog route (Fixed)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="mtop_8x8_route" id="mtop_8x8_route">
                                    <label class="form-check-label" for="mtop_8x8_route">MTOP # 8x8 City Proper/Outside Route (Fixed)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="mtop_dashboard" id="mtop_dashboard">
                                    <label class="form-check-label" for="mtop_dashboard">MTOP # dash board/Name of operator's</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="signal_light_left" id="signal_light_left">
                                    <label class="form-check-label" for="signal_light_left">Signal light/left</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="signal_light_right" id="signal_light_right">
                                    <label class="form-check-label" for="signal_light_right">Signal light/right</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="head_light" id="head_light">
                                    <label class="form-check-label" for="head_light">Head light</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="stop_light" id="stop_light">
                                    <label class="form-check-label" for="stop_light">stop light</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="side_mirror" id="side_mirror">
                                    <label class="form-check-label" for="side_mirror">Side Mirror left & right</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="horn" id="horn">
                                    <label class="form-check-label" for="horn">Horn</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="standard_muffler" id="standard_muffler">
                                    <label class="form-check-label" for="standard_muffler">Standard Muffler</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="color_coding" id="color_coding">
                                    <label class="form-check-label" for="color_coding">Color coding</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox py-2">
                                    <input class="form-check-input" type="checkbox" name="tarifa" id="tarifa">
                                    <label class="form-check-label" for="tarifa">Tarifa</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-4 fw-bold text-primary"><i class="bi bi-person-check me-2"></i>Verification</h6>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Inspected by <span class="text-danger">*</span></label>
                                <input type="text" name="inspected_by" class="form-control" required value="<?= esc(session()->get('name')) ?>" placeholder="Officer Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="inspection_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= base_url('inspections') ?>" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">Save Inspection Report</button>
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

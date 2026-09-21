<!-- Locations Management Page -->
<div class="row g-4">
    <!-- Countries -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h6><i class="bi bi-globe me-2"></i>Countries</h6>
                <?php if (Session::isAdmin()): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCountryModal"><i class="bi bi-plus"></i></button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <table class="table"><thead><tr><th>Name</th><th>Code</th><th></th></tr></thead><tbody>
                    <?php foreach ($countries as $c): ?>
                    <tr>
                        <td class="fw-semibold"><?= Validator::e($c['name']) ?></td>
                        <td><code><?= Validator::e($c['code']) ?></code></td>
                        <td><?php if (Session::isAdmin()): ?><button class="btn btn-icon btn-sm btn-outline-danger" onclick="deleteLoc('countries',<?= $c['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>

    <!-- Cities -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h6><i class="bi bi-buildings me-2"></i>Cities</h6>
                <?php if (Session::isAdmin()): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCityModal"><i class="bi bi-plus"></i></button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <table class="table"><thead><tr><th>City</th><th>Country</th><th></th></tr></thead><tbody>
                    <?php foreach ($allCities as $c): ?>
                    <tr>
                        <td class="fw-semibold"><?= Validator::e($c['name']) ?></td>
                        <td><small class="text-muted"><?= Validator::e($c['country_name']) ?></small></td>
                        <td><?php if (Session::isAdmin()): ?><button class="btn btn-icon btn-sm btn-outline-danger" onclick="deleteLoc('cities',<?= $c['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>

    <!-- Areas -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h6><i class="bi bi-pin-map me-2"></i>Areas</h6>
                <?php if (Session::isAdmin()): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAreaModal"><i class="bi bi-plus"></i></button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0" style="max-height:500px;overflow-y:auto;">
                <table class="table"><thead><tr><th>Area</th><th>City</th><th></th></tr></thead><tbody>
                    <?php foreach ($allAreas as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= Validator::e($a['name']) ?></td>
                        <td><small class="text-muted"><?= Validator::e($a['city_name']) ?></small></td>
                        <td><?php if (Session::isAdmin()): ?><button class="btn btn-icon btn-sm btn-outline-danger" onclick="deleteLoc('areas',<?= $a['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<!-- Add Country Modal -->
<div class="modal fade" id="addCountryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--border-radius);">
    <div class="modal-header"><h6 class="modal-title fw-bold">Add Country</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Country Name</label><input type="text" class="form-control" id="countryName"></div>
        <div class="mb-3"><label class="form-label">Country Code</label><input type="text" class="form-control" id="countryCode" maxlength="3" placeholder="e.g. BD"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary" onclick="addCountry()">Add</button></div>
</div></div></div>

<!-- Add City Modal -->
<div class="modal fade" id="addCityModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--border-radius);">
    <div class="modal-header"><h6 class="modal-title fw-bold">Add City</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Country</label><select class="form-select" id="cityCountryId">
            <?php foreach ($countries as $c): ?><option value="<?= $c['id'] ?>"><?= Validator::e($c['name']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="mb-3"><label class="form-label">City Name</label><input type="text" class="form-control" id="cityName"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary" onclick="addCity()">Add</button></div>
</div></div></div>

<!-- Add Area Modal -->
<div class="modal fade" id="addAreaModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--border-radius);">
    <div class="modal-header"><h6 class="modal-title fw-bold">Add Area</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">City</label><select class="form-select" id="areaCityId">
            <?php foreach ($allCities as $c): ?><option value="<?= $c['id'] ?>"><?= Validator::e($c['name']) ?> (<?= Validator::e($c['country_name']) ?>)</option><?php endforeach; ?>
        </select></div>
        <div class="mb-3"><label class="form-label">Area Name</label><input type="text" class="form-control" id="areaName"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary" onclick="addArea()">Add</button></div>
</div></div></div>

<script>
function addCountry() {
    apiPost('/api/countries', { name: document.getElementById('countryName').value, code: document.getElementById('countryCode').value })
        .then(res => { showToast(res.message, res.success?'success':'error'); if(res.success) location.reload(); });
}
function addCity() {
    apiPost('/api/cities', { country_id: document.getElementById('cityCountryId').value, name: document.getElementById('cityName').value })
        .then(res => { showToast(res.message, res.success?'success':'error'); if(res.success) location.reload(); });
}
function addArea() {
    apiPost('/api/areas', { city_id: document.getElementById('areaCityId').value, name: document.getElementById('areaName').value })
        .then(res => { showToast(res.message, res.success?'success':'error'); if(res.success) location.reload(); });
}
function deleteLoc(type, id) {
    if (!confirm('Delete this '+type.slice(0,-1)+'?')) return;
    apiDelete('/api/'+type+'/'+id).then(res => { showToast(res.message, res.success?'success':'error'); if(res.success) location.reload(); });
}
</script>

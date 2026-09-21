<!-- Categories Management Page -->
<div class="content-card">
    <div class="card-header">
        <h6><i class="bi bi-tags me-2"></i>Business Categories</h6>
        <?php if (Session::isAdmin()): ?>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus me-1"></i>Add Category
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Variants</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><i class="bi <?= Validator::e($cat['icon']) ?>" style="font-size:20px;color:var(--primary);"></i></td>
                        <td class="fw-semibold"><?= Validator::e($cat['name']) ?></td>
                        <td><code><?= Validator::e($cat['slug']) ?></code></td>
                        <td><small class="text-muted"><?= Validator::e($cat['variants'] ? implode(', ', json_decode($cat['variants'], true) ?: []) : '—') ?></small></td>
                        <td><span class="badge bg-<?= $cat['is_active'] ? 'success' : 'secondary' ?>"><?= $cat['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <?php if (Session::isAdmin()): ?>
                            <button class="btn btn-icon btn-sm btn-outline-danger" onclick="deleteCategory(<?= $cat['id'] ?>)"><i class="bi bi-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:var(--border-radius);">
        <div class="modal-header"><h6 class="modal-title fw-bold">Add Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label fw-semibold">Name</label><input type="text" class="form-control" id="catName" placeholder="e.g. Restaurant"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Icon</label><input type="text" class="form-control" id="catIcon" value="bi-building" placeholder="Bootstrap Icon class"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Variants (comma separated)</label><input type="text" class="form-control" id="catVariants" placeholder="restaurant, family restaurant"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" onclick="addCategory()">Add Category</button></div>
    </div></div>
</div>

<script>
function addCategory() {
    const name = document.getElementById('catName').value.trim();
    if (!name) return;
    const variants = document.getElementById('catVariants').value.split(',').map(v => v.trim()).filter(Boolean);
    apiPost('/api/categories', { name, icon: document.getElementById('catIcon').value, variants }).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) location.reload();
    });
}

function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    apiDelete('/api/categories/' + id).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) location.reload();
    });
}
</script>

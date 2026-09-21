<!-- Users Management Page -->
<div class="content-card">
    <div class="card-header">
        <h6><i class="bi bi-person-gear me-2"></i>User Management</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus me-1"></i>Add User
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Username</th><th>Email</th><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-semibold"><?= Validator::e($u['username']) ?></td>
                        <td><?= Validator::e($u['email']) ?></td>
                        <td><?= Validator::e($u['full_name'] ?? '—') ?></td>
                        <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td><small class="text-muted"><?= $u['last_login'] ? date('M d, H:i', strtotime($u['last_login'])) : 'Never' ?></small></td>
                        <td>
                            <?php if ((int) $u['id'] !== Session::userId()): ?>
                            <button class="btn btn-icon btn-sm btn-outline-danger" onclick="deleteUser(<?= $u['id'] ?>)"><i class="bi bi-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--border-radius);">
    <div class="modal-header"><h6 class="modal-title fw-bold">Add User</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" id="newUsername"></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" id="newEmail"></div>
        <div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" id="newFullName"></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" id="newPassword"></div>
        <div class="mb-3"><label class="form-label">Role</label>
            <select class="form-select" id="newRole"><option value="staff">Staff</option><option value="admin">Admin</option></select>
        </div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary" onclick="addUser()">Create User</button></div>
</div></div></div>

<script>
function addUser() {
    apiPost('/api/users', {
        username: document.getElementById('newUsername').value,
        email: document.getElementById('newEmail').value,
        full_name: document.getElementById('newFullName').value,
        password: document.getElementById('newPassword').value,
        role: document.getElementById('newRole').value
    }).then(res => { showToast(res.message, res.success?'success':'error'); if(res.success) location.reload(); });
}

function deleteUser(id) {
    if (!confirm('Delete this user?')) return;
    apiDelete('/api/users/' + id).then(res => { showToast(res.message, res.success?'success':'error'); if(res.success) location.reload(); });
}
</script>

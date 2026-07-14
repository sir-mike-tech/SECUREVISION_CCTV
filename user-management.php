<?php include 'includes/header.php'; ?>
<div class="grid grid-2">
  <div class="card">
    <h3>User Administration</h3>
    <div class="form-row">
      <div class="field"><label>Name</label><input class="input" value="Alicia Brooks"></div>
      <div class="field"><label>Email</label><input class="input" value="alicia@securevision.com"></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Role</label><select class="select"><option>Administrator</option><option>Security Officer</option><option>Viewer</option></select></div>
      <div class="field"><label>Password</label><input class="input" type="password" value="********"></div>
    </div>
    <div style="margin-top: 14px;"><button class="button">Add User</button></div>
  </div>
  <div class="card">
    <h3>Existing Users</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <tr><td>James Carter</td><td>Administrator</td><td><span class="badge success">Active</span></td><td><a href="#">Edit</a></td></tr>
          <tr><td>Rina Shah</td><td>Security Officer</td><td><span class="badge info">Active</span></td><td><a href="#">Edit</a></td></tr>
          <tr><td>Owen Price</td><td>Viewer</td><td><span class="badge warning">Pending</span></td><td><a href="#">Edit</a></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>


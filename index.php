<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecureVision Login</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="login-page">
    <div class="login-card">
      <div class="brand">
        <h2>SecureVision</h2>
        <p>Professional CCTV Monitoring Platform</p>
      </div>
      <h2>Secure Access</h2>
      <p>Sign in to manage cameras, alerting, and monitoring workflows securely.</p>
      <form>
        <div class="field">
          <label for="username">Username</label>
          <input class="input" id="username" placeholder="Enter username">
        </div>
        <div class="field" style="margin-top: 12px;">
          <label for="password">Password</label>
          <input class="input" id="password" type="password" placeholder="Enter password">
        </div>
        <div class="login-actions">
          <label class="muted"><input type="checkbox"> Remember me</label>
          <a class="muted" href="#">Forgot password?</a>
        </div>
        <div style="margin-top: 16px;">
          <a class="button" href="dashboard.php">Login</a>
        </div>
      </form>
      <p class="footer-note">Security notice: All access is logged and monitored for compliance.</p>
    </div>
  </div>
</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Placement Management System - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Campus Placement System</h1>
                <p class="login-subtitle">Sign in to your account</p>
            </div>
            
            <form id="loginForm" method="POST" action="api/login.php" data-validate>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Enter your email"
                        required
                        data-field="Email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                        data-field="Password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-secondary">Demo Credentials:</p>
                <div style="font-size: 0.875rem; color: #64748b;">
                    <strong>Developer:</strong> vishalsharma08555252@gmail.com / Vishal@178<br>
                    <em>Note: Other accounts will be created through the developer panel</em>
                </div>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Signing in...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Utils.showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    Utils.showAlert(data.message, 'error');
                }
            } catch (error) {
                Utils.showAlert('An error occurred. Please try again.', 'error');
                console.error('Login error:', error);
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
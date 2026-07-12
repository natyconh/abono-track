<?php
// _legacy/abono-track/app/views/users/login.php
// Página de login de Abono Track — adaptado desde Ryzoma Agro
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['titulo'] ?? 'Iniciar Sesión'; ?> - Abono Track</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css">
    <style>
    body.login-page {
        background: linear-gradient(135deg, #1a3a2a 0%, #2d5a3d 50%, #1a3a2a 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card-glass {
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 1rem;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        color: white;
        padding: 2.5rem !important;
    }
    .login-card-glass .brand-name {
        font-size: 2rem;
        font-weight: 700;
        color: #f8f9fa;
        letter-spacing: -0.5px;
    }
    .login-card-glass .brand-dot { color: #F7B538; }
    .login-card-glass .tagline {
        color: rgba(255,255,255,0.75);
        font-size: 0.95rem;
        margin-bottom: 2rem;
    }
    .login-card-glass .form-floating > .form-control {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
    }
    .login-card-glass .form-floating > .form-control:focus {
        background: rgba(255,255,255,0.28);
        border-color: rgba(255,255,255,0.5);
        box-shadow: 0 0 0 0.25rem rgba(255,255,255,0.15);
        color: #fff;
    }
    .login-card-glass .form-floating > label { color: rgba(255,255,255,0.8); }
    .btn-accent-calendula {
        background-color: #F7B538; border-color: #F7B538; color: #313131;
    }
    .btn-accent-calendula:hover { background-color: #e0a331; border-color: #e0a331; color: #313131; }
    </style>
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg login-card-glass">
                    <div class="card-body p-4 p-sm-5">

                        <div class="text-center mb-4">
                            <div class="mb-3" style="font-size: 3rem;">🌿</div>
                            <div class="brand-name">abono<span class="brand-dot">·</span>track</div>
                            <div class="tagline">Gestión de Fertirrigación</div>
                        </div>

                        <?php SessionHelper::displayFlash(); ?>

                        <?php if (!empty($data['error'])): ?>
                            <div class="alert alert-danger text-center p-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($data['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo URL_ROOT; ?>/users/login" method="POST" novalidate>

                            <div class="form-floating mb-3">
                                <input type="text"
                                    class="form-control <?php echo !empty($data['username_err']) ? 'is-invalid' : ''; ?>"
                                    id="username" name="username"
                                    value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
                                <label for="username"><i class="bi bi-person me-2"></i>Usuario</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password"
                                    class="form-control <?php echo !empty($data['password_err']) ? 'is-invalid' : ''; ?>"
                                    id="password" name="password" required>
                                <label for="password"><i class="bi bi-lock me-2"></i>Contraseña</label>
                            </div>

                            <div class="d-grid mt-4">
                                <button class="btn btn-accent-calendula btn-lg fw-bold" type="submit">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Ingresar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// app/views/users/login.php
// VERSIÓN 2.0 - Con identidad "Solarpunk"
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $data['titulo'] ?? 'Login'; ?> - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css"> 
    
    <style>
body.login-page {
    background-image: url('<?php echo URL_ROOT; ?>/img/login-bg.jpg');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative; /* Necesario para el overlay */
}

/* Overlay oscuro para opacar el fondo */
body.login-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.4); /* Capa oscura, 40% de opacidad */
    z-index: 1; /* Asegura que esté sobre el fondo pero debajo del login card */
}

/* La tarjeta de login, ahora con z-index más alto */
.login-card-glass {
    background: rgba(255, 255, 255, 0.15); /* Fondo semi-transparente */
    backdrop-filter: blur(10px); /* El efecto "esmerilado" */
    -webkit-backdrop-filter: blur(10px); /* Soporte para Safari */
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1); /* Sombra sutil */
    color: white; /* Hacemos el texto de la tarjeta blanco */
    position: relative; /* Necesario para que el z-index funcione */
    z-index: 2; /* Asegura que la tarjeta esté sobre el overlay */
    padding: 2.5rem !important; /* Aumentamos el padding para más espacio */
}

/* Estilos para el logo con mix-blend-mode */

.login-card-glass .logo-img {
    background-color: palegoldenrod; /* 1. Crea el fondo de círculo blanco */
    border-radius: 40%;       /* 2. Lo hace un círculo */
    padding: 0.5rem;         /* 3. Crea el espacio INTERNO, empujando el logo hacia el centro */
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); /* 4. Le da sombra para que "flote" */
    
    /* 5. Define un tamaño fijo y cuadrado para el círculo */
    width: 160px;
    height: 160px;
    
    /* 6. (LA CLAVE) Asegura que tu imagen (src) se muestre contenida
          dentro del 'padding', sin estirarse ni deformarse. */
    object-fit: contain;      
}

.login-card-glass h3.site-name { /* Añadimos una clase para el nombre del sitio */
    color: #f8f9fa; /* Color blanco más brillante para el nombre del sitio */
    font-weight: 600; /* Más énfasis */
    margin-top: 1rem;
    margin-bottom: 0.5rem; /* Ajuste de margen */
}

.login-card-glass .tagline { /* Nuevo: para "Gestión Agrícola" */
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.1rem;
    margin-bottom: 2rem; /* Más espacio debajo */
}

/* Ajustes a los inputs flotantes de Bootstrap */
.login-card-glass .form-floating > .form-control {
    background-color: rgba(255, 255, 255, 0.2); /* Menos opaco, más transparente */
    border: 1px solid rgba(255, 255, 255, 0.3); /* Borde sutil */
    color: #fff; /* Texto del input en blanco */
    height: calc(3.5rem + 2px); /* Ajuste de altura para mejor visual */
}

.login-card-glass .form-floating > .form-control:focus {
    background-color: rgba(255, 255, 255, 0.4); /* Un poco más sólido al enfocar */
    border-color: rgba(255, 255, 255, 0.5); /* Borde más visible */
    box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25); /* Sombra de foco más suave */
    color: #fff; /* Asegura el color blanco al enfocar */
}

.login-card-glass .form-floating > label {
    color: rgba(255, 255, 255, 0.8); /* Labels blancos y semi-transparentes */
}

/* Para el botón, si no está definido globalmente en style.css */
.btn-accent-calendula {
    background-color: #F7B538; /* Color naranja Solarpunk */
    border-color: #F7B538;
    color: #313131; /* Texto oscuro para contraste */
}

.btn-accent-calendula:hover {
    background-color: #e0a331; /* Tono más oscuro al pasar el mouse */
    border-color: #e0a331;
    color: #313131;
}
    </style>
</head>
<body class="login-page"> 

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5"> 
                
                <div class="card shadow-lg border-0 rounded-lg overflow-hidden login-card-glass">
                <div class="card-body p-4 p-sm-5">
    
                <div class="text-center mb-4">
                            <!-- EL CAMBIO CLAVE: Círculo CSS con la Y -->
                            <div class="logo-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <span class="brand-y-iso">y</span>
                            </div>
                            
                            <!-- Nombre de la Marca (Texto) -->
                            <span class="site-name">ryzoma</span>
                <span class="badge bg-light text-secondary ms-2 fw-normal border" style="font-size: 0.6rem; vertical-align: middle;">AGRO</span>
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
                            <input type="text" class="form-control <?php echo !empty($data['username_err']) ? 'is-invalid' : ''; ?>" id="username" name="username" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
                            <label for="username"><i class="bi bi-person me-2"></i>Usuario</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control <?php echo !empty($data['password_err']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
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

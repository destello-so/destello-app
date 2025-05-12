<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/home.css">
    <title>Papelería Destello - Acceso</title>
    <style>
        body {
            background-color: #f8f6fa;
            background-image: linear-gradient(120deg, #f8f6fa 0%, #fff9fa 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(to right, #feaa9d, #f7859c);
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(254, 170, 157, 0.25);
            margin-bottom: 20px;
        }
        
        .navbar-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }
        
        .navbar-logo a {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .navbar-logo h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 4px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .logo-tagline {
            color: white;
            font-size: 0.9rem;
            font-style: italic;
            opacity: 0.9;
        }
        
        .auth-container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(107, 122, 214, 0.15);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            min-height: 600px;
        }
        
        .auth-sidebar {
            flex: 0 0 45%;
            background: linear-gradient(to bottom, #f7859c 15%, #feaa9d 85%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/img/pattern.png');
            background-size: cover;
            opacity: 0.07;
            z-index: 1;
        }
        
        .auth-sidebar-content {
            position: relative;
            z-index: 2;
        }
        
        .auth-sidebar h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 800;
        }
        
        .auth-sidebar p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .auth-form-container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            font-size: 2.4rem;
            color: #f7859c;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }
        
        .auth-header h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #feaa9d, #f7859c);
            border-radius: 3px;
        }
        
        .auth-header p {
            color: #888;
            font-size: 1.1rem;
            margin-top: 15px;
        }
        
        .auth-form {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 0;
        }
        
        .auth-form-group {
            margin-bottom: 20px;
            position: relative;
            flex: 1;
        }
        
        .auth-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .auth-form-group input {
            width: 100%;
            padding: 16px 14px 16px 50px;
            border: 1.5px solid #ffd6d6;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            outline: none;
            background-color: #fff9fa;
        }
        
        .auth-form-group input:focus {
            border-color: #feaa9d;
            box-shadow: 0 0 0 3px rgba(254, 170, 157, 0.15);
            background-color: #fff;
        }
        
        .auth-form-group i {
            position: absolute;
            top: 45px;
            left: 18px;
            color: #feaa9d;
            font-size: 1.1rem;
        }
        
        .auth-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(90deg, #6b7ad6, #f7859c);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 0 4px 15px rgba(254, 170, 157, 0.25);
            letter-spacing: 0.5px;
        }
        
        .auth-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, #f7859c, #6b7ad6);
            transition: all 0.4s;
            z-index: -1;
        }
        
        .auth-button:hover::before {
            left: 0;
        }
        
        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(254, 170, 157, 0.3);
        }
        
        .auth-divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #888;
        }
        
        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .auth-divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }
        
        .auth-switch {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .auth-switch a {
            color: #6b7ad6;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .auth-switch a:hover {
            text-decoration: underline;
        }
        
        .social-buttons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-button {
            flex: 1;
            padding: 15px;
            border: 1.5px solid #ffd6d6;
            border-radius: 12px;
            font-size: 1rem;
            text-align: center;
            background: white;
            color: #333;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .social-button:hover {
            border-color: #feaa9d;
            background: #f9f9f9;
        }
        
        .social-button i {
            margin-right: 8px;
        }
        
        .auth-tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }
        
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 18px;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            letter-spacing: 0.5px;
        }
        
        .auth-tab.active {
            color: #f7859c;
        }
        
        .auth-tab.active::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -1px;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #feaa9d, #f7859c);
            border-radius: 3px 3px 0 0;
        }
        
        #register-form {
            display: none;
        }
        
        @media (max-width: 992px) {
            .auth-container {
                width: 90%;
                flex-direction: column;
                margin: 20px auto;
            }
            
            .auth-sidebar {
                display: none;
            }
            
            .auth-form-container {
                padding: 30px 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        .fade {
            animation: fadeEffect 0.5s;
        }
        
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <a href="home.php">
                    <h1>Destello</h1>
                    <span class="logo-tagline">Creatividad en papel</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-sidebar">
            <div class="auth-sidebar-content">
                <h2>Bienvenido a Papelería Destello</h2>
                <p>Descubre nuestra exclusiva colección de artículos escolares, de oficina y manualidades para dar rienda suelta a tu creatividad.</p>
                <p>Únete a nuestra comunidad y disfruta de ofertas exclusivas, productos únicos y mucho más.</p>
                <div style="margin-top: 30px;">
                    <i class="fas fa-book" style="font-size: 2.5rem; margin-right: 15px;"></i>
                    <i class="fas fa-pencil-alt" style="font-size: 2.5rem; margin-right: 15px;"></i>
                    <i class="fas fa-paint-brush" style="font-size: 2.5rem;"></i>
                </div>
            </div>
        </div>
        
        <div class="auth-form-container">
            <div class="auth-tabs">
                <div class="auth-tab active" id="login-tab">Iniciar Sesión</div>
                <div class="auth-tab" id="register-tab">Registrarse</div>
            </div>
            
            <div id="login-form" class="fade">
                <div class="auth-header">
                    <h1>Iniciar Sesión</h1>
                    <p>Accede a tu cuenta para continuar</p>
                </div>
                
                <form class="auth-form" id="login-form-submit">
                    <div class="auth-form-group">
                        <label for="email">Correo Electrónico</label>
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="tucorreo@ejemplo.com" required>
                    </div>
                    
                    <div class="auth-form-group">
                        <label for="password">Contraseña</label>
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" style="margin-right: 8px;">
                            <span style="font-size: 0.9rem; color: #666;">Recordarme</span>
                        </label>
                        <a href="#" style="color: #6b7ad6; text-decoration: none; font-size: 0.9rem;">¿Olvidaste tu contraseña?</a>
                    </div>
                    
                    <button type="submit" class="auth-button">Iniciar Sesión</button>
                </form>
                
                <div class="auth-divider">
                    <span>O continúa con</span>
                </div>
                
                <div class="social-buttons">
                    <button class="social-button">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button class="social-button">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </button>
                </div>
                
                <div class="auth-switch">
                    ¿No tienes una cuenta? <a href="#" id="show-register">Regístrate</a>
                </div>
            </div>
            
            <div id="register-form" class="fade">
                <div class="auth-header">
                    <h1>Crear Cuenta</h1>
                    <p>Únete a nuestra comunidad</p>
                </div>
                
                <form class="auth-form" action="register_process.php" method="post">
                    <div class="form-row">
                        <div class="auth-form-group">
                            <label for="reg-nombre">Nombre</label>
                            <i class="fas fa-user"></i>
                            <input type="text" id="reg-nombre" name="nombre" placeholder="Tu nombre" required>
                        </div>
                        
                        <div class="auth-form-group">
                            <label for="reg-apellido">Apellido</label>
                            <i class="fas fa-user"></i>
                            <input type="text" id="reg-apellido" name="apellido" placeholder="Tu apellido" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="auth-form-group">
                            <label for="reg-celular">Celular</label>
                            <i class="fas fa-mobile-alt"></i>
                            <input type="tel" id="reg-celular" name="celular" placeholder="Tu número de celular" required>
                        </div>
                        
                        <div class="auth-form-group">
                            <label for="reg-email">Correo Electrónico</label>
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="reg-email" name="email" placeholder="tucorreo@ejemplo.com" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="auth-form-group">
                            <label for="reg-password">Contraseña</label>
                            <i class="fas fa-lock"></i>
                            <input type="password" id="reg-password" name="password" placeholder="Crea una contraseña" required>
                        </div>
                        
                        <div class="auth-form-group">
                            <label for="reg-confirm-password">Confirmar Contraseña</label>
                            <i class="fas fa-lock"></i>
                            <input type="password" id="reg-confirm-password" name="confirm_password" placeholder="Confirma tu contraseña" required>
                        </div>
                    </div>
                    
                    <label style="display: flex; align-items: flex-start; cursor: pointer; margin-bottom: 20px;">
                        <input type="checkbox" style="margin-right: 10px; margin-top: 5px;" required>
                        <span style="font-size: 0.9rem; color: #666; line-height: 1.5;">
                            Acepto los <a href="#" style="color: #6b7ad6;">Términos y Condiciones</a> y la <a href="#" style="color: #6b7ad6;">Política de Privacidad</a>.
                        </span>
                    </label>
                    
                    <button type="submit" class="auth-button">Crear Cuenta</button>
                </form>
                
                <div class="auth-switch">
                    ¿Ya tienes una cuenta? <a href="#" id="show-login">Inicia Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <footer style="background: linear-gradient(to right, #feaa9d, #f7859c); color: white; padding: 30px 0; margin-top: auto;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; text-align: center;">
            <p style="margin-bottom: 10px;">© 2023 Papelería Destello. Todos los derechos reservados.</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 15px;">
                <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-facebook-f"></i></a>
                <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                <a href="#" style="color: white; font-size: 1.2rem;"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const showRegister = document.getElementById('show-register');
            const showLogin = document.getElementById('show-login');
            const notification = document.getElementById('notification');
            
            function showLoginForm() {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
            }
            
            function showRegisterForm() {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                loginTab.classList.remove('active');
                registerTab.classList.add('active');
            }
            
            loginTab.addEventListener('click', showLoginForm);
            registerTab.addEventListener('click', showRegisterForm);
            showRegister.addEventListener('click', function(e) {
                e.preventDefault();
                showRegisterForm();
            });
            showLogin.addEventListener('click', function(e) {
                e.preventDefault();
                showLoginForm();
            });
            
            // Mostrar notificación
            function showNotification(message, type = 'error') {
                notification.textContent = message;
                notification.className = 'notification ' + type;
                notification.classList.add('show');
                
                // Ocultar la notificación después de 5 segundos
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 5000);
            }
            
            // Procesamiento del formulario de login
            document.getElementById('login-form-submit').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const form = this;
                
                // Mostrar efecto de carga
                form.classList.add('form-loading');
                
                fetch('api/login_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    form.classList.remove('form-loading');
                    
                    if (data.success) {
                        // Guardar el ID del usuario en localStorage
                        localStorage.setItem('userId', data.userId);
                        localStorage.setItem('userName', data.userName);
                        
                        // Mostrar notificación de éxito
                        showNotification(data.message, 'success');
                        
                        // Redirigir después de un breve retraso
                        setTimeout(() => {
                            // Verificar si hay una URL de redirección almacenada
                            const redirectUrl = localStorage.getItem('redirectAfterLogin') || data.redirectUrl;
                            
                            // Limpiar la URL de redirección después de usarla
                            if (localStorage.getItem('redirectAfterLogin')) {
                                localStorage.removeItem('redirectAfterLogin');
                            }
                            
                            window.location.href = redirectUrl;
                        }, 1500);
                    } else {
                        // Mostrar mensaje de error
                        showNotification(data.message);
                    }
                })
                .catch(error => {
                    form.classList.remove('form-loading');
                    showNotification('Error de conexión. Intente nuevamente más tarde.');
                    console.error('Error:', error);
                });
            });
            
            // Procesamiento del formulario de registro
            document.getElementById('register-form-submit').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const form = this;
                
                // Verificar que las contraseñas coincidan
                const password = formData.get('password');
                const confirmPassword = formData.get('confirm_password');
                
                if (password !== confirmPassword) {
                    showNotification('Las contraseñas no coinciden');
                    return;
                }
                
                // Mostrar efecto de carga
                form.classList.add('form-loading');
                
                fetch('api/register_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    form.classList.remove('form-loading');
                    
                    if (data.success) {
                        // Guardar el ID del usuario en localStorage
                        localStorage.setItem('userId', data.userId);
                        localStorage.setItem('userName', data.userName);
                        
                        // Mostrar notificación de éxito
                        showNotification(data.message, 'success');
                        
                        // Redirigir después de un breve retraso
                        setTimeout(() => {
                            window.location.href = data.redirectUrl;
                        }, 1500);
                    } else {
                        // Mostrar mensaje de error
                        showNotification(data.message);
                    }
                })
                .catch(error => {
                    form.classList.remove('form-loading');
                    showNotification('Error de conexión. Intente nuevamente más tarde.');
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>

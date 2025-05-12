<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-logo">
            <a href="home.php">
                <h1>Destello</h1>
                <span class="logo-tagline">Ilumina tu estilo</span>
            </a>
        </div>
        
        <div class="navbar-links">
            <a href="home.php" class="nav-link">
                <i class="fas fa-home"></i> Inicio
            </a>
            <!-- Elementos solo visibles para usuarios logeados -->
            <div class="logged-in-only" style="display: none;">
                <a href="wishlist.php" class="nav-link">
                    <i class="fas fa-heart"></i> Lista de Deseos
                </a>
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-box"></i> Mis Pedidos
                </a>
            </div>
        </div>
        
        <div class="navbar-actions">
            <!-- Elementos solo visibles para usuarios logeados -->
            <div class="logged-in-only" style="display: none;">
                <div class="action-icons">
                    <a href="cart.php" class="icon-action">
                        <div class="icon-badge-container">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge">0</span>
                        </div>
                    </a>
                </div>
                <div class="user-info">
                    <span id="user-name"></span>
                </div>
                <button id="logout-button" class="navbar-button">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
            
            <!-- Elementos solo visibles para usuarios no logeados -->
            <div class="logged-out-only" style="display: none;">
                <a href="login.php" class="navbar-button">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
            </div>
        </div>
        
        <div class="navbar-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>
</nav>

<div class="mobile-menu-container">
    <ul class="mobile-menu">
        <li><a href="home.php"><i class="fas fa-home"></i> Inicio</a></li>
        
        <!-- Elementos móviles solo visibles para usuarios logeados -->
        <div class="logged-in-only" style="display: none;">
            <li><a href="products.php"><i class="fas fa-shopping-bag"></i> Productos</a></li>
            <li><a href="wishlist.php"><i class="fas fa-heart"></i> Lista de Deseos</a></li>
            <li><a href="orders.php"><i class="fas fa-box"></i> Mis Pedidos</a></li>
            <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
            <li><button id="mobile-logout-button" class="mobile-button"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button></li>
        </div>
        
        <!-- Elementos móviles solo visibles para usuarios no logeados -->
        <div class="logged-out-only" style="display: none;">
            <li><a href="login.php" class="mobile-button"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
        </div>
    </ul>
</div>

<script>
    // Menú móvil
    const mobileMenu = document.querySelector('#mobile-menu');
    const mobileMenuContainer = document.querySelector('.mobile-menu-container');
    
    mobileMenu.addEventListener('click', function() {
        mobileMenu.classList.toggle('is-active');
        mobileMenuContainer.classList.toggle('active');
    });
    
    // Verificar estado de login
    document.addEventListener('DOMContentLoaded', function() {
        const userId = localStorage.getItem('userId');
        const userName = localStorage.getItem('userName');
        const loggedInElements = document.querySelectorAll('.logged-in-only');
        const loggedOutElements = document.querySelectorAll('.logged-out-only');
        const userNameElement = document.getElementById('user-name');
        const logoutButton = document.getElementById('logout-button');
        const mobileLogoutButton = document.getElementById('mobile-logout-button');
        const cartBadge = document.querySelector('.badge');
        
        if (userId) {
            // Usuario logeado
            loggedInElements.forEach(el => el.style.display = 'flex');
            loggedOutElements.forEach(el => el.style.display = 'none');
            
            // Mostrar nombre de usuario
            if (userNameElement && userName) {
                userNameElement.textContent = userName;
            }
            
            // Función para cerrar sesión
            const handleLogout = function() {
                localStorage.removeItem('userId');
                localStorage.removeItem('userName');
                window.location.href = 'login.php';
            };
            
            // Asignar evento de cerrar sesión
            if (logoutButton) {
                logoutButton.addEventListener('click', handleLogout);
            }
            
            if (mobileLogoutButton) {
                mobileLogoutButton.addEventListener('click', handleLogout);
            }
            
            // Actualizar el contador del carrito si existe la función
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            } else if (cartBadge) {
                // Si no existe la función, intentar obtener el conteo del carrito
                fetch(`api/get_cart_totals.php?userId=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.itemCount > 0) {
                            cartBadge.textContent = data.itemCount;
                            cartBadge.style.display = 'flex';
                        } else {
                            cartBadge.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar el carrito:', error);
                        cartBadge.style.display = 'none';
                    });
            }
        } else {
            // Usuario no logeado
            loggedInElements.forEach(el => el.style.display = 'none');
            loggedOutElements.forEach(el => el.style.display = 'flex');
        }
    });
</script>

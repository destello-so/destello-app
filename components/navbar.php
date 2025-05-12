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
            <a href="wishlist.php" class="nav-link">
                <i class="fas fa-heart"></i> Lista de Deseos
            </a>
            <a href="orders.php" class="nav-link">
                <i class="fas fa-box"></i> Mis Pedidos
            </a>
        </div>
        
        <div class="navbar-actions">
            <div class="action-icons">
                <a href="cart.php" class="icon-action">
                    <div class="icon-badge-container">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge">3</span>
                    </div>
                </a>
            </div>
            <a href="logout.php" class="navbar-button">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
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
        <li><a href="products.php"><i class="fas fa-shopping-bag"></i> Productos</a></li>
        <li><a href="wishlist.php"><i class="fas fa-heart"></i> Lista de Deseos</a></li>
        <li><a href="orders.php"><i class="fas fa-box"></i> Mis Pedidos</a></li>
        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
        <li><a href="logout.php" class="mobile-button"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
    </ul>
</div>

<script>
    const mobileMenu = document.querySelector('#mobile-menu');
    const mobileMenuContainer = document.querySelector('.mobile-menu-container');
    
    mobileMenu.addEventListener('click', function() {
        mobileMenu.classList.toggle('is-active');
        mobileMenuContainer.classList.toggle('active');
    });
</script>

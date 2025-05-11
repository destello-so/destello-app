<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-logo">
            <a href="home.php">
                <h1>Destello</h1>
                <span class="logo-tagline">Ilumina tu estilo</span>
            </a>
        </div>
        
        <div class="navbar-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        
        <ul class="navbar-menu">
            <li class="navbar-item">
                <a href="home.php" class="navbar-link"><i class="fas fa-home"></i> Inicio</a>
            </li>
            <li class="navbar-item">
                <a href="cart.php" class="navbar-link"><i class="fas fa-shopping-cart"></i> Carrito</a>
            </li>
            <li class="navbar-item">
                <a href="wishlist.php" class="navbar-link"><i class="fas fa-heart"></i> Lista de deseos</a>
            </li>
            <li class="navbar-item">
                <a href="checkout.php" class="navbar-link"><i class="fas fa-credit-card"></i> Pago</a>
            </li>
            <li class="navbar-item navbar-btn">
                <a href="logout.php" class="navbar-button"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>
</nav>

<script>
    const mobileMenu = document.querySelector('#mobile-menu');
    const navbarMenu = document.querySelector('.navbar-menu');
    
    mobileMenu.addEventListener('click', function() {
        mobileMenu.classList.toggle('is-active');
        navbarMenu.classList.toggle('active');
    });
</script>

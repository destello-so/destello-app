<?php
session_start();
require 'config/db.php';

// Obtener parámetros de filtro
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$price_range = isset($_GET['price']) ? $_GET['price'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Configuración de paginación
$products_per_page = 12;
$offset = ($page - 1) * $products_per_page;

// Construir la consulta SQL base - Se han eliminado los alias para mayor claridad
$query = "
    SELECT 
        products.id, 
        products.name, 
        products.description, 
        products.price, 
        products.stock_quantity, 
        products.created_at,
        product_images.url as image_url, 
        product_images.alt_text,
        GROUP_CONCAT(DISTINCT categories.name ORDER BY categories.name SEPARATOR ', ') as categories,
        GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id SEPARATOR ',') as category_ids
    FROM 
        products
    LEFT JOIN 
        product_images ON products.id = product_images.product_id AND product_images.is_primary = 1
    LEFT JOIN 
        product_categories ON products.id = product_categories.product_id
    LEFT JOIN 
        categories ON product_categories.category_id = categories.id
";

// Condiciones de filtrado
$conditions = [];
$params = [];

if ($category > 0) {
    $conditions[] = "product_categories.category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $conditions[] = "(products.name LIKE ? OR products.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($price_range)) {
    list($min, $max) = explode('-', $price_range);
    if ($max == 'max') {
        $conditions[] = "products.price >= ?";
        $params[] = $min;
    } else {
        $conditions[] = "products.price BETWEEN ? AND ?";
        $params[] = $min;
        $params[] = $max;
    }
}

// Agregar condiciones a la consulta
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Agrupar por producto
$query .= " GROUP BY products.id, products.name, products.description, products.price, products.stock_quantity, products.created_at, product_images.url, product_images.alt_text";

// Ordenamiento
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY products.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY products.price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY products.name ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY products.name DESC";
        break;
    case 'popular':
        // Asumimos que hay una forma de medir popularidad, por ahora es aleatorio
        $query .= " ORDER BY RAND()";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY products.created_at DESC";
        break;
}

// Consulta para contar el total de productos (para paginación)
$count_query = preg_replace('/SELECT\s+.+?\sFROM\s/is', 'SELECT COUNT(DISTINCT products.id) FROM ', $query);
$count_query = preg_replace('/GROUP BY.+$/is', '', $count_query);

try {
    // Ejecutar consulta de conteo
    $count_stmt = $conn->prepare($count_query);
    foreach ($params as $key => $param) {
        $count_stmt->bindValue($key + 1, $param);
    }
    $count_stmt->execute();
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $products_per_page);
    
    // Agregar límite para paginación
    $query .= " LIMIT $offset, $products_per_page";
    
    // Ejecutar consulta principal
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $param) {
        $stmt->bindValue($key + 1, $param);
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener todas las categorías para el filtro
    $categories_stmt = $conn->query("
        SELECT id, name, 
        (SELECT COUNT(*) FROM product_categories WHERE category_id = categories.id) as product_count 
        FROM categories 
        ORDER BY name");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/home.css">

    <title>Destello - Descubre nuestra colección</title>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <main class="home-container">
        <!-- Banner Hero -->
        <section class="hero-banner" style="background-image: url('assets/img/banner-summer.png'); background-size: cover; background-position: center;">
            <div class="hero-content">
                <h1>Ilumina tu creatividad</h1>
                <p>Descubre nuestra colección exclusiva de productos que harán brillar tus ideas</p>
                <a href="#products-section" class="hero-button">Explorar Ahora</a>
            </div>
        </section>
        
        <!-- Promoción de verano -->
        <section class="promo-banner">
            <img src="assets/img/banner-summer.png" alt="Promo de Verano - 20% de descuento con el código summer20" class="promo-image">
            <div class="promo-overlay">
                <div class="promo-content">
                    <h2 class="promo-title">PROMO DE VERANO</h2>
                    <p class="promo-description">20% de descuento con el code summer20</p>
                    <a href="#products-section" class="promo-button">¡Aprovecha ahora!</a>
                </div>
            </div>
        </section>
        
        <!-- Sección de bienvenida -->
        <section class="welcome-section">
            <img src="assets/img/banner-welcome.png" alt="Bienvenido a Destello" class="welcome-image">
            <div class="welcome-content">
                <h2>Bienvenido a Destello</h2>
                <p>Tu destino para productos papelería de alta calidad</p>
            </div>
        </section>
        
        <!-- Categorías destacadas -->
        <section class="featured-categories">
            <h2>Categorías Destacadas</h2>
            <div class="categories-container">
                <?php foreach(array_slice($categories, 0, 6) as $cat): ?>
                <a href="?category=<?php echo $cat['id']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-<?php 
                            // Asignar un icono según la categoría
                            $icons = ['pen-fancy', 'book', 'palette', 'paperclip', 'briefcase', 'gift'];
                            echo $icons[array_rand($icons)]; 
                        ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <span class="product-count"><?php echo $cat['product_count']; ?> productos</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Productos -->
        <section class="products-section" id="products-section">
            <div class="section-header">
                <h2>Nuestra Colección</h2>
                <p>Encontramos <?php echo $total_products; ?> productos para ti</p>
            </div>
            
            <!-- Filtros y ordenación -->
            <div class="filters-container">
                <form action="" method="GET" id="filter-form" class="filter-form">
                    <div class="search-filter">
                        <input type="text" name="search" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="filter-options">
                        <div class="filter-group">
                            <label for="category">Categoría:</label>
                            <select name="category" id="category">
                                <option value="0">Todas las categorías</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['product_count']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="price">Precio:</label>
                            <select name="price" id="price">
                                <option value="">Cualquier precio</option>
                                <option value="0-50" <?php echo ($price_range == '0-50') ? 'selected' : ''; ?>>Menos de S/50</option>
                                <option value="50-100" <?php echo ($price_range == '50-100') ? 'selected' : ''; ?>>S/50 - S/100</option>
                                <option value="100-200" <?php echo ($price_range == '100-200') ? 'selected' : ''; ?>>S/100 - S/200</option>
                                <option value="200-max" <?php echo ($price_range == '200-max') ? 'selected' : ''; ?>>Más de S/200</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort">Ordenar por:</label>
                            <select name="sort" id="sort">
                                <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Más recientes</option>
                                <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                                <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                                <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Nombre: A-Z</option>
                                <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Nombre: Z-A</option>
                                <option value="popular" <?php echo ($sort == 'popular') ? 'selected' : ''; ?>>Popularidad</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="filter-button">Aplicar Filtros</button>
                        <a href="home.php" class="reset-button"><i class="fas fa-undo"></i> Reiniciar</a>
                    </div>
                </form>
            </div>
            
            <!-- Grid de productos -->
            <div class="products-grid">
                <?php if (count($products) > 0): ?>
                    <?php foreach($products as $product): ?>
                    <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                        <div class="product-badge <?php echo $product['stock_quantity'] < 5 ? 'low-stock' : ''; ?>">
                            <?php if($product['stock_quantity'] < 5 && $product['stock_quantity'] > 0): ?>
                                ¡Últimas unidades!
                            <?php elseif($product['stock_quantity'] <= 0): ?>
                                Agotado
                            <?php else: ?>
                                En stock
                            <?php endif; ?>
                        </div>
                        
                        <div class="wishlist-button" data-product-id="<?php echo $product['id']; ?>">
                            <i class="far fa-heart"></i>
                        </div>
                        
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-image">
                            <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/300x300'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['alt_text'] ?? $product['name']); ?>">
                        </a>
                        
                        <div class="product-info">
                            <div class="product-categories">
                                <?php 
                                $cat_names = explode(', ', $product['categories']);
                                foreach(array_slice($cat_names, 0, 2) as $cat_name): ?>
                                    <span class="category-tag"><?php echo htmlspecialchars($cat_name); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <h3 class="product-name">
                                <a href="product.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <div class="product-description">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 80) . (strlen($product['description']) > 80 ? '...' : '')); ?>
                            </div>
                            
                            <div class="product-price">
                                <span class="price">S/ <?php echo number_format($product['price'], 2); ?></span>
                            </div>
                            
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="view-button">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                                
                                <button class="add-to-cart-button" data-product-id="<?php echo $product['id']; ?>"
                                    <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart"></i> 
                                    <?php echo $product['stock_quantity'] <= 0 ? 'Agotado' : 'Añadir'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <div class="no-products-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No encontramos productos</h3>
                        <p>Intenta con otros filtros o reinicia la búsqueda</p>
                        <a href="home.php" class="reset-button"><i class="fas fa-undo"></i> Ver todos los productos</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&category=<?php echo $category; ?>&price=<?php echo $price_range; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php
                    // Determinar qué páginas mostrar
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    // Mostrar primera página y puntos suspensivos si es necesario
                    if ($start_page > 1) {
                        echo '<a href="?page=1&category=' . $category . '&price=' . $price_range . '&search=' . urlencode($search) . '&sort=' . $sort . '" class="page-number">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="page-ellipsis">...</span>';
                        }
                    }
                    
                    // Mostrar páginas numeradas
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = $i == $page ? 'active' : '';
                        echo '<a href="?page=' . $i . '&category=' . $category . '&price=' . $price_range . '&search=' . urlencode($search) . '&sort=' . $sort . '" class="page-number ' . $active . '">' . $i . '</a>';
                    }
                    
                    // Mostrar última página y puntos suspensivos si es necesario
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="page-ellipsis">...</span>';
                        }
                        echo '<a href="?page=' . $total_pages . '&category=' . $category . '&price=' . $price_range . '&search=' . urlencode($search) . '&sort=' . $sort . '" class="page-number">' . $total_pages . '</a>';
                    }
                    ?>
                </div>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&category=<?php echo $category; ?>&price=<?php echo $price_range; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-link">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
        
        <!-- Ventajas -->
        <section class="benefits-section">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Envío Rápido</h3>
                <p>Entrega en 24-48 horas a todo el país</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Pago Seguro</h3>
                <p>Tus datos siempre protegidos</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h3>Devoluciones</h3>
                <p>30 días para cambios y devoluciones</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Atención 24/7</h3>
                <p>Estamos siempre disponibles para ti</p>
            </div>
        </section>
        
        <!-- Newsletter -->
        <section class="newsletter-section">
            <div class="newsletter-content">
                <h2>¡Únete a nuestra comunidad!</h2>
                <p>Suscríbete para recibir novedades, ofertas exclusivas y contenido especial</p>
                
                <form class="newsletter-form">
                    <div class="form-group">
                        <input type="email" placeholder="Tu correo electrónico" required>
                        <button type="submit">Suscribirme</button>
                    </div>
                    <div class="form-privacy">
                        <input type="checkbox" id="privacy-policy" required>
                        <label for="privacy-policy">Acepto la política de privacidad y recibir comunicaciones</label>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <!-- Modal de producto visto rápido -->
    <div class="quick-view-modal" id="quickViewModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="quick-view-content" id="quickViewContent">
                <!-- El contenido se cargará dinámicamente -->
            </div>
        </div>
    </div>
    
    <!-- Notificación Toast -->
    <div class="toast-container">
        <div class="toast" id="toast">
            <span class="toast-message"></span>
            <button class="toast-close">&times;</button>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        // Inicialización de Notyf para notificaciones
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top',
            },
            types: [
                {
                    type: 'success',
                    background: 'linear-gradient(to right, #feaa9d, #f7859c)',
                    icon: {
                        className: 'fas fa-check',
                        tagName: 'i',
                        color: 'white'
                    }
                },
                {
                    type: 'error',
                    background: '#FF7272',
                    icon: {
                        className: 'fas fa-times',
                        tagName: 'i',
                        color: 'white'
                    }
                }
            ]
        });
        
        // Filtros y ordenamiento con actualización en tiempo real
        document.querySelectorAll('#category, #price, #sort').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filter-form').submit();
            });
        });
        
        // Función para agregar al carrito
        document.querySelectorAll('.add-to-cart-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                
                // Verificar si el usuario está autenticado
                <?php if (isset($_SESSION['user_id'])): ?>
                    // Hacer la petición AJAX para agregar al carrito
                    fetch('api/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `userId=<?php echo $_SESSION['user_id']; ?>&productId=${productId}&quantity=1`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notyf.success('¡Producto añadido al carrito!');
                            
                            // Actualizar contador del carrito en el navbar si existe
                            const cartBadge = document.querySelector('.badge');
                            if (cartBadge) {
                                const currentCount = parseInt(cartBadge.textContent);
                                cartBadge.textContent = currentCount + 1;
                            }
                        } else {
                            notyf.error(data.message || 'Error al añadir el producto');
                        }
                    })
                    .catch(error => {
                        notyf.error('Error de conexión');
                        console.error('Error:', error);
                    });
                <?php else: ?>
                    // Redirigir al login
                    notyf.error('Debes iniciar sesión para añadir productos al carrito');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                <?php endif; ?>
            });
        });
        
        // Función para añadir a la lista de deseos
        document.querySelectorAll('.wishlist-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const heartIcon = this.querySelector('i');
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    // Hacer la petición AJAX para agregar/quitar de la lista de deseos
                    fetch('api/toggle_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `userId=<?php echo $_SESSION['user_id']; ?>&productId=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.action === 'added') {
                                heartIcon.classList.remove('far');
                                heartIcon.classList.add('fas');
                                heartIcon.parentElement.classList.add('active');
                                notyf.success('¡Producto añadido a tu lista de deseos!');
                            } else {
                                heartIcon.classList.remove('fas');
                                heartIcon.classList.add('far');
                                heartIcon.parentElement.classList.remove('active');
                                notyf.success('Producto eliminado de tu lista de deseos');
                            }
                        } else {
                            notyf.error(data.message || 'Error al actualizar la lista de deseos');
                        }
                    })
                    .catch(error => {
                        notyf.error('Error de conexión');
                        console.error('Error:', error);
                    });
                <?php else: ?>
                    // Redirigir al login
                    notyf.error('Debes iniciar sesión para añadir a tu lista de deseos');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                <?php endif; ?>
            });
        });
        
        // Verificar productos en la lista de deseos del usuario actual
        <?php if (isset($_SESSION['user_id'])): ?>
            // Obtener todos los IDs de productos en la página
            const productIds = Array.from(document.querySelectorAll('.product-card')).map(card => card.getAttribute('data-product-id')).join(',');
            
            if (productIds.length > 0) {
                // Verificar cuáles están en la lista de deseos
                fetch(`api/check_wishlist.php?userId=<?php echo $_SESSION['user_id']; ?>&productIds=${productIds}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.wishlistItems.length > 0) {
                        // Marcar los botones de wishlist para productos que ya están en la lista
                        data.wishlistItems.forEach(productId => {
                            const wishlistButton = document.querySelector(`.wishlist-button[data-product-id="${productId}"]`);
                            if (wishlistButton) {
                                const heartIcon = wishlistButton.querySelector('i');
                                heartIcon.classList.remove('far');
                                heartIcon.classList.add('fas');
                                wishlistButton.classList.add('active');
                            }
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        <?php endif; ?>
        
        // Efecto de scroll suave para el botón "Explorar Ahora"
        document.querySelector('.hero-button').addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            window.scrollTo({
                top: targetElement.offsetTop - 100,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>



<?php
require '../config/db.php';

// Verificar que se recibió el ID de usuario
if (!isset($_GET['userId']) || empty($_GET['userId'])) {
    echo '<div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Tu carrito está vacío</h2>
            <p>Parece que aún no has iniciado sesión</p>
            <a href="login.php" class="shop-now-btn">Iniciar Sesión</a>
        </div>';
    exit;
}

$userId = (int)$_GET['userId'];

try {
    // Primero verificamos si el usuario existe
    $userStmt = $conn->prepare("SELECT id, first_name, last_name FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    
    if ($userStmt->rowCount() === 0) {
        echo '<div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Usuario no encontrado</h2>
                <p>No se encontró información para este usuario</p>
                <a href="login.php" class="shop-now-btn">Iniciar Sesión</a>
            </div>';
        exit;
    }
    
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Buscamos el carrito del usuario
    $cartStmt = $conn->prepare("
        SELECT id, created_at FROM carts 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $cartStmt->execute([$userId]);
    
    if ($cartStmt->rowCount() === 0) {
        // Si no tiene carrito, creamos uno
        $createCartStmt = $conn->prepare("
            INSERT INTO carts (user_id, created_at, updated_at) 
            VALUES (?, NOW(), NOW())
        ");
        $createCartStmt->execute([$userId]);
        $cartId = $conn->lastInsertId();
    } else {
        $cartData = $cartStmt->fetch(PDO::FETCH_ASSOC);
        $cartId = $cartData['id'];
    }
    
    // Obtenemos los items del carrito con toda la información del producto
    $itemsStmt = $conn->prepare("
        SELECT ci.id as cart_item_id, ci.quantity, ci.cart_id, ci.added_at,
               p.id as product_id, p.name, p.description, p.price, p.stock_quantity, p.sku, 
               pi.url as image_url, pi.alt_text,
               c.name as category_name, c.id as category_id
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE ci.cart_id = ?
    ");
    $itemsStmt->execute([$cartId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // MODIFICACIÓN: Verificar cuáles de estos productos están en la lista de deseos
    $productIds = [];
    foreach ($items as $item) {
        $productIds[] = $item['product_id'];
    }

    $wishlistItems = [];
    if (!empty($productIds)) {
        // Buscar la wishlist del usuario
        $wishlistStmt = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? LIMIT 1");
        $wishlistStmt->execute([$userId]);
        
        if ($wishlistStmt->rowCount() > 0) {
            $wishlistId = $wishlistStmt->fetchColumn();
            
            // Preparar placeholders para la consulta SQL
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            
            // Verificar qué productos están en la wishlist
            $wishlistCheckStmt = $conn->prepare("
                SELECT product_id 
                FROM wishlist_items 
                WHERE wishlist_id = ? AND product_id IN ($placeholders)
            ");
            
            $params = [$wishlistId];
            foreach ($productIds as $id) {
                $params[] = $id;
            }
            
            $wishlistCheckStmt->execute($params);
            $wishlistResults = $wishlistCheckStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($wishlistResults as $result) {
                $wishlistItems[] = $result['product_id'];
            }
        }
    }
    // FIN DE LA MODIFICACIÓN
    
    if (count($items) === 0) {
        echo '<div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Tu carrito está vacío</h2>
                <p>Parece que aún no has añadido productos a tu carrito</p>
                <a href="home.php" class="shop-now-btn">Explorar Productos</a>
            </div>';
        exit;
    }
    
    // Calculamos los totales
    $subtotal = 0;
    $discount = 0; // Aquí implementarías la lógica de descuentos
    $itemCount = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $itemCount += $item['quantity'];
    }
    
    $total = $subtotal - $discount;
    
    // Aquí comienza la estructura mejorada para coincidir con la imagen 2
    echo '<div class="cart-items-container">';
    
    // Mostrar los items del carrito primero
    foreach ($items as $item) {
        $stockClass = $item['stock_quantity'] < 5 ? 'low-stock' : '';
        $dateAdded = new DateTime($item['added_at']);
        $inWishlist = in_array($item['product_id'], $wishlistItems);
        
        echo '<div class="cart-item ' . $stockClass . '" data-cart-item-id="' . $item['cart_item_id'] . '" data-product-id="' . $item['product_id'] . '">
                <div class="item-image">
                    <img src="' . ($item['image_url'] ?? 'https://via.placeholder.com/100x120/ffb6c1/ffffff') . '" alt="' . htmlspecialchars($item['alt_text'] ?? $item['name']) . '">
                </div>
                <div class="item-details">
                    <h3>' . htmlspecialchars($item['name']) . '</h3>
                    <p class="item-description">' . htmlspecialchars(substr($item['description'], 0, 100) . (strlen($item['description']) > 100 ? '...' : '')) . '</p>
                    <div class="item-meta">
                        <div class="item-category">Categoría: <span>' . htmlspecialchars($item['category_name'] ?? 'Sin categoría') . '</span></div>
                        <div class="item-sku">SKU: <span>' . htmlspecialchars($item['sku']) . '</span></div>
                    </div>
                </div>
                <div class="item-quantity">
                    <button class="quantity-btn decrease-btn" ' . ($item['stock_quantity'] < 1 ? 'disabled' : '') . '><i class="fas fa-minus"></i></button>
                    <input type="number" value="' . $item['quantity'] . '" min="1" max="' . min(10, $item['stock_quantity']) . '" class="quantity-input">
                    <button class="quantity-btn increase-btn" ' . ($item['quantity'] >= $item['stock_quantity'] || $item['quantity'] >= 10 ? 'disabled' : '') . '><i class="fas fa-plus"></i></button>
                    ' . ($item['stock_quantity'] < 5 ? '<div class="stock-warning">¡Quedan solo ' . $item['stock_quantity'] . '!</div>' : '') . '
                </div>
                <div class="item-price">
                    <div class="current-price">S/ ' . number_format($item['price'], 2) . '</div>
                    <div class="item-total">Total: S/ ' . number_format($item['price'] * $item['quantity'], 2) . '</div>
                </div>
                <div class="item-actions">
                    <button class="action-btn wishlist-btn ' . ($inWishlist ? 'in-wishlist' : '') . '" title="' . ($inWishlist ? 'En tu lista de deseos' : 'Mover a lista de deseos') . '" data-in-wishlist="' . ($inWishlist ? 'true' : 'false') . '">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="action-btn delete-btn" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>';
    }
    
    echo '</div>'; // Cierre de cart-items-container
    
    // Sección lateral con cupón y resumen
    echo '<div class="cart-sidebar">';
    
    // Cupón de descuento
    echo '<div class="coupon-section">
            <h3><i class="fas fa-ticket-alt"></i> Código de Descuento</h3>
            <div class="coupon-form">
                <input type="text" placeholder="Ingresa tu código" class="coupon-input">
                <button class="apply-coupon">Aplicar</button>
            </div>
        </div>';
    
    // Resumen del pedido con más detalles
    echo '<div class="order-summary">
            <h3>Resumen del Pedido</h3>
            <div class="summary-item">
                <span>Subtotal (' . $itemCount . ' ' . ($itemCount == 1 ? 'producto' : 'productos') . ')</span>
                <span>S/ ' . number_format($subtotal, 2) . '</span>
            </div>
            <div class="summary-item">
                <span>Descuento</span>
                <span>' . ($discount > 0 ? '-' : '') . 'S/ ' . number_format($discount, 2) . '</span>
            </div>
            <div class="summary-item">
                <span>Envío</span>
                <span>Gratis</span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span>S/ ' . number_format($total, 2) . '</span>
            </div>
            <a href="checkout.php" class="checkout-btn">
                <i class="fas fa-credit-card"></i> Proceder al Pago
            </a>
            <a href="home.php" class="continue-shopping">
                <i class="fas fa-arrow-left"></i> Seguir Comprando
            </a>
        </div>';
    
    echo '</div>'; // Cierre de cart-sidebar
    
    // Adjuntar los event listeners
    echo '<script>
        attachCartEventListeners();
    </script>';
    
} catch (PDOException $e) {
    echo '<div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <p>Error al cargar el carrito: ' . $e->getMessage() . '</p>
        </div>';
}
?>

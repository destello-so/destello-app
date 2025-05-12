<?php
session_start();
require 'config/db.php';

// Verificar que existe un ID de producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: home.php');
    exit;
}

$product_id = (int)$_GET['id'];

try {
    // Obtener información del producto
    $product_query = "
        SELECT 
            p.id, 
            p.name, 
            p.description, 
            p.price, 
            p.sku,
            p.stock_quantity,
            p.weight,
            p.dimensions,
            p.created_at,
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as categories,
            GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
        FROM 
            products p
        LEFT JOIN 
            product_categories pc ON p.id = pc.product_id
        LEFT JOIN 
            categories c ON pc.category_id = c.id
        WHERE 
            p.id = ?
        GROUP BY 
            p.id
    ";
    
    $stmt = $conn->prepare($product_query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        // Si el producto no existe
        header('Location: home.php');
        exit;
    }
    
    // Obtener imágenes del producto
    $images_query = "
        SELECT id, url, alt_text, is_primary 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY is_primary DESC, id ASC
    ";
    
    $images_stmt = $conn->prepare($images_query);
    $images_stmt->execute([$product_id]);
    $images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay imágenes, usar una por defecto
    if (empty($images)) {
        $images = [
            [
                'id' => 0,
                'url' => 'https://via.placeholder.com/600x600?text=Sin+imagen',
                'alt_text' => $product['name'],
                'is_primary' => 1
            ]
        ];
    }
    
    // Obtener productos relacionados (de las mismas categorías)
    $related_query = "
        SELECT DISTINCT 
            p.id, 
            p.name, 
            p.price, 
            p.stock_quantity,
            pi.url as image_url,
            pi.alt_text
        FROM 
            products p
        JOIN 
            product_categories pc ON p.id = pc.product_id
        JOIN 
            product_categories pc2 ON pc.category_id = pc2.category_id
        LEFT JOIN 
            product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE 
            pc2.product_id = ? AND p.id != ?
        GROUP BY 
            p.id, p.name, p.price, p.stock_quantity, pi.url, pi.alt_text
        ORDER BY 
            RAND()
        LIMIT 4
    ";
    
    $related_stmt = $conn->prepare($related_query);
    $related_stmt->execute([$product_id, $product_id]);
    $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar si el producto está en la lista de deseos del usuario
    $in_wishlist = false;
    if (isset($_SESSION['user_id'])) {
        $wishlist_query = "
            SELECT COUNT(*) FROM wishlist_items wi
            JOIN wishlists w ON wi.wishlist_id = w.id
            WHERE w.user_id = ? AND wi.product_id = ?
        ";
        $wishlist_stmt = $conn->prepare($wishlist_query);
        $wishlist_stmt->execute([$_SESSION['user_id'], $product_id]);
        $in_wishlist = ($wishlist_stmt->fetchColumn() > 0);
    }
    
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
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/product.css">
    <title>Destello - <?php echo htmlspecialchars($product['name']); ?></title>   
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <main class="product-container">
        <div class="breadcrumb">
            <a href="home.php">Inicio</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <?php if (strpos($product['categories'], ',') !== false): 
                $main_category = explode(',', $product['categories'])[0];
                $main_category_id = explode(',', $product['category_ids'])[0];
            ?>
                <a href="home.php?category=<?php echo $main_category_id; ?>"><?php echo htmlspecialchars($main_category); ?></a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
            <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
        
        <div class="product-detail">
            <div class="product-gallery">
                <div class="main-image">
                    <img id="main-product-image" src="<?php echo htmlspecialchars($images[0]['url']); ?>" alt="<?php echo htmlspecialchars($images[0]['alt_text']); ?>">
                    <?php if ($product['stock_quantity'] < 5 && $product['stock_quantity'] > 0): ?>
                        <div class="product-badge low-stock">¡Últimas unidades!</div>
                    <?php elseif ($product['stock_quantity'] <= 0): ?>
                        <div class="product-badge out-of-stock">Agotado</div>
                    <?php endif; ?>
                    <button class="zoom-button" id="zoom-button"><i class="fas fa-search-plus"></i></button>
                </div>
                
                <?php if (count($images) > 1): ?>
                <div class="thumbnails">
                    <?php foreach($images as $index => $image): ?>
                    <div class="thumbnail <?php echo ($index === 0) ? 'active' : ''; ?>" data-image="<?php echo htmlspecialchars($image['url']); ?>" data-alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                        <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-meta">
                    <div class="sku">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                    <div class="categories">
                        <span>Categorías:</span>
                        <?php 
                        $cats = explode(', ', $product['categories']);
                        $cat_ids = explode(',', $product['category_ids']);
                        foreach(array_combine($cat_ids, $cats) as $id => $name): 
                        ?>
                            <a href="home.php?category=<?php echo $id; ?>" class="category-tag"><?php echo htmlspecialchars($name); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="product-price">
                    <span class="price">S/ <?php echo number_format($product['price'], 2); ?></span>
                </div>
                
                <div class="stock-info <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                    <i class="fas <?php echo $product['stock_quantity'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php if ($product['stock_quantity'] > 10): ?>
                        <span>En stock</span>
                    <?php elseif ($product['stock_quantity'] > 0): ?>
                        <span>¡Solo quedan <?php echo $product['stock_quantity']; ?> unidades!</span>
                    <?php else: ?>
                        <span>Agotado</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                <div class="quantity-selector">
                    <label for="quantity">Cantidad:</label>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                        <button type="button" class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                
                <div class="product-actions">
                    <button class="add-to-cart-button" data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart"></i> Añadir al carrito
                    </button>
                    
                    <button class="wishlist-button <?php echo $in_wishlist ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                        <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                </div>
                <?php else: ?>
                <div class="product-actions">
                    <button class="notify-button">
                        <i class="fas fa-bell"></i> Notificarme cuando esté disponible
                    </button>
                    
                    <button class="wishlist-button <?php echo $in_wishlist ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                        <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="additional-info">
                    <div class="shipping-info">
                        <i class="fas fa-truck"></i>
                        <span>Envío en 24-48h</span>
                    </div>
                    <div class="return-info">
                        <i class="fas fa-undo"></i>
                        <span>30 días para devoluciones</span>
                    </div>
                    <div class="secure-info">
                        <i class="fas fa-lock"></i>
                        <span>Pago seguro</span>
                    </div>
                </div>
                
                <div class="share-buttons">
                    <span>Compartir:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="share-button facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&text=<?php echo urlencode($product['name']); ?>" target="_blank" class="share-button twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&media=<?php echo urlencode($images[0]['url']); ?>&description=<?php echo urlencode($product['name']); ?>" target="_blank" class="share-button pinterest">
                        <i class="fab fa-pinterest-p"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($product['name'] . " - http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="share-button whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="product-tabs">
            <div class="tabs-nav">
                <button class="tab-button active" data-tab="details">Detalles</button>
                <button class="tab-button" data-tab="shipping">Envío y devoluciones</button>
                <button class="tab-button" data-tab="reviews">Reseñas</button>
            </div>
            
            <div class="tab-content active" id="details-tab">
                <h3>Especificaciones del Producto</h3>
                <table class="specifications">
                    <tr>
                        <th>SKU</th>
                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                    </tr>
                    <?php if (!empty($product['weight'])): ?>
                    <tr>
                        <th>Peso</th>
                        <td><?php echo htmlspecialchars($product['weight']); ?> kg</td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($product['dimensions'])): ?>
                    <tr>
                        <th>Dimensiones</th>
                        <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Categorías</th>
                        <td><?php echo htmlspecialchars($product['categories']); ?></td>
                    </tr>
                </table>
                
                <div class="product-full-description">
                    <h3>Descripción Detallada</h3>
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            
            <div class="tab-content" id="shipping-tab">
                <h3>Información de Envío</h3>
                <p>Realizamos envíos a todo el país en un plazo de 24-48 horas laborables desde la confirmación del pedido.</p>
                <p>Los gastos de envío son de S/ 10 para pedidos inferiores a S/ 100. Para pedidos superiores a S/ 100, el envío es gratuito.</p>
                
                <h3>Política de Devoluciones</h3>
                <p>Dispones de 30 días desde la recepción del pedido para realizar devoluciones o cambios de producto.</p>
                <p>Para procesar una devolución, el producto debe estar en perfectas condiciones y con su embalaje original.</p>
                <p>Contacta con nuestro servicio de atención al cliente para iniciar el proceso de devolución.</p>
            </div>
            
            <div class="tab-content" id="reviews-tab">
                <h3>Reseñas de Clientes</h3>
                <p class="no-reviews">Aún no hay reseñas para este producto. ¡Sé el primero en opinar!</p>
                
                <div class="write-review">
                    <h4>Escribe tu opinión</h4>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <form id="review-form">
                        <div class="rating-stars">
                            <span>Tu valoración:</span>
                            <div class="stars">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="rating-value" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="review-title">Título:</label>
                            <input type="text" id="review-title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="review-content">Tu opinión:</label>
                            <textarea id="review-content" name="content" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-review">Enviar Reseña</button>
                    </form>
                    <?php else: ?>
                    <p class="login-to-review">Debes <a href="login.php">iniciar sesión</a> para escribir una reseña.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Productos relacionados -->
        <?php if (!empty($related_products)): ?>
        <section class="related-products">
            <h2>También te puede interesar</h2>
            
            <div class="related-products-grid">
                <?php foreach($related_products as $related): ?>
                <div class="product-card" data-product-id="<?php echo $related['id']; ?>">
                    <?php if($related['stock_quantity'] < 5 && $related['stock_quantity'] > 0): ?>
                        <div class="product-badge low-stock">¡Últimas unidades!</div>
                    <?php elseif($related['stock_quantity'] <= 0): ?>
                        <div class="product-badge out-of-stock">Agotado</div>
                    <?php endif; ?>
                    
                    <a href="product.php?id=<?php echo $related['id']; ?>" class="product-image">
                        <img src="<?php echo !empty($related['image_url']) ? $related['image_url'] : 'https://via.placeholder.com/300x300'; ?>" 
                             alt="<?php echo htmlspecialchars($related['alt_text'] ?? $related['name']); ?>">
                    </a>
                    
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="product.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-price">
                            <span class="price">S/ <?php echo number_format($related['price'], 2); ?></span>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="view-button">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>
    
    <!-- Modal de imagen ampliada -->
    <div id="image-modal" class="modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modal-image">
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
        
        // Cambio de imagen principal al hacer clic en miniaturas
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-image');
                const imgAlt = this.getAttribute('data-alt');
                const mainImage = document.getElementById('main-product-image');
                
                mainImage.src = imgSrc;
                mainImage.alt = imgAlt;
                
                // Actualizar miniatura activa
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Control de cantidad
        const quantityInput = document.getElementById('quantity');
        const minusBtn = document.querySelector('.quantity-btn.minus');
        const plusBtn = document.querySelector('.quantity-btn.plus');
        
        if (minusBtn && plusBtn) {
            minusBtn.addEventListener('click', () => {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
            
            plusBtn.addEventListener('click', () => {
                const currentValue = parseInt(quantityInput.value);
                const maxValue = parseInt(quantityInput.getAttribute('max'));
                if (currentValue < maxValue) {
                    quantityInput.value = currentValue + 1;
                }
            });
            
            quantityInput.addEventListener('change', () => {
                let value = parseInt(quantityInput.value);
                const maxValue = parseInt(quantityInput.getAttribute('max'));
                
                if (isNaN(value) || value < 1) {
                    value = 1;
                } else if (value > maxValue) {
                    value = maxValue;
                    notyf.error(`Solo hay ${maxValue} unidades disponibles`);
                }
                
                quantityInput.value = value;
            });
        }
        
        // Tabs de información
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remover clase active de todos los botones y contenidos
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Agregar clase active al botón y contenido seleccionado
                button.classList.add('active');
                const tabId = `${button.getAttribute('data-tab')}-tab`;
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Rating stars
        document.querySelectorAll('.rating-stars .stars i').forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                highlightStars(rating);
            });
            
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                document.getElementById('rating-value').value = rating;
                highlightStars(rating);
            });
            
            star.addEventListener('mouseout', function() {
                const currentRating = parseInt(document.getElementById('rating-value').value);
                highlightStars(currentRating);
            });
        });
        
        function highlightStars(rating) {
            document.querySelectorAll('.rating-stars .stars i').forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }
        
        // Modal de imagen ampliada
        const modal = document.getElementById('image-modal');
        const modalImg = document.getElementById('modal-image');
        const mainImg = document.getElementById('main-product-image');
        const zoomBtn = document.getElementById('zoom-button');
        
        zoomBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            modalImg.src = mainImg.src;
        });
        
        mainImg.addEventListener('click', function() {
            modal.style.display = 'block';
            modalImg.src = this.src;
        });
        
        document.querySelector('.close-modal').addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Añadir al carrito
        const addToCartBtn = document.querySelector('.add-to-cart-button');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const quantity = document.getElementById('quantity').value;
                
                // Verificar si el usuario está autenticado
                <?php if (isset($_SESSION['user_id'])): ?>
                    // Hacer la petición AJAX para agregar al carrito
                    fetch('api/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `userId=<?php echo $_SESSION['user_id']; ?>&productId=${productId}&quantity=${quantity}`
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
        }
        
        // Añadir a lista de deseos
        const wishlistBtn = document.querySelector('.wishlist-button');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                
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
                                this.classList.add('active');
                                this.querySelector('i').classList.remove('far');
                                this.querySelector('i').classList.add('fas');
                                notyf.success('¡Producto añadido a tu lista de deseos!');
                            } else {
                                this.classList.remove('active');
                                this.querySelector('i').classList.remove('fas');
                                this.querySelector('i').classList.add('far');
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
        }
        
        // Notificación de disponibilidad
        const notifyBtn = document.querySelector('.notify-button');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', function() {
                <?php if (isset($_SESSION['user_id'])): ?>
                    notyf.success('Te notificaremos cuando el producto esté disponible');
                    // Aquí iría la lógica para guardar la solicitud de notificación
                <?php else: ?>
                    notyf.error('Debes iniciar sesión para recibir notificaciones');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                <?php endif; ?>
            });
        }
        
        // Envío de reseñas
        const reviewForm = document.getElementById('review-form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const rating = document.getElementById('rating-value').value;
                const title = document.getElementById('review-title').value;
                const content = document.getElementById('review-content').value;
                
                if (rating === '0') {
                    notyf.error('Por favor, elige una valoración');
                    return;
                }
                
                // Simulación de envío de reseña
                notyf.success('¡Gracias por tu reseña! Se publicará tras ser revisada');
                reviewForm.reset();
                highlightStars(0);
            });
        }
    </script>
</body>
</html>
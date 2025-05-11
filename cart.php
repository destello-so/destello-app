<?php
session_start();
require 'config/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/cart.css">
    <title>Destello - Carrito</title>   
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="cart-container">
        <div class="cart-header">
            <div class="header-decoration">
                <i class="fas fa-sparkles"></i>
            </div>
            <h1>Tu Carrito de Compras</h1>
            <p>Estos artículos están esperando convertirse en parte de tu colección</p>
        </div>
        
        <div class="cart-content">
            <?php if (true) { /* Cambiar por condición real de carrito no vacío */ ?>
                <div class="cart-items">
                    <!-- Item 1 -->
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="https://via.placeholder.com/100x120/ffb6c1/ffffff" alt="Producto 1">
                        </div>
                        <div class="item-details">
                            <h3>Collar Destello Rosa</h3>
                            <p class="item-description">Collar elegante con cristales rosados</p>
                            <div class="item-category"><span>Categoría:</span> Collares</div>
                        </div>
                        <div class="item-quantity">
                            <button class="quantity-btn decrease-btn"><i class="fas fa-minus"></i></button>
                            <input type="number" value="1" min="1" max="10" class="quantity-input">
                            <button class="quantity-btn increase-btn"><i class="fas fa-plus"></i></button>
                        </div>
                        <div class="item-price">
                            <div class="current-price">S/ 29.99</div>
                            <div class="original-price">S/ 39.99</div>
                        </div>
                        <div class="item-actions">
                            <button class="action-btn wishlist-btn" title="Mover a lista de deseos" data-in-wishlist="false">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="action-btn delete-btn" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Item 2 (En lista de deseos) -->
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="https://via.placeholder.com/100x120/ffcccb/ffffff" alt="Producto 2">
                        </div>
                        <div class="item-details">
                            <h3>Pendientes Flor Cristal</h3>
                            <p class="item-description">Pendientes elegantes con forma de flor</p>
                            <div class="item-category"><span>Categoría:</span> Pendientes</div>
                        </div>
                        <div class="item-quantity">
                            <button class="quantity-btn decrease-btn"><i class="fas fa-minus"></i></button>
                            <input type="number" value="2" min="1" max="10" class="quantity-input">
                            <button class="quantity-btn increase-btn"><i class="fas fa-plus"></i></button>
                        </div>
                        <div class="item-price">
                            <div class="current-price">S/ 24.99</div>
                        </div>
                        <div class="item-actions">
                            <button class="action-btn wishlist-btn in-wishlist" title="En tu lista de deseos" data-in-wishlist="true">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="action-btn delete-btn" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Item 3 -->
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="https://via.placeholder.com/100x120/fadadd/ffffff" alt="Producto 3">
                        </div>
                        <div class="item-details">
                            <h3>Pulsera Perlas Delicada</h3>
                            <p class="item-description">Pulsera de perlas con broche de plata</p>
                            <div class="item-category"><span>Categoría:</span> Pulseras</div>
                        </div>
                        <div class="item-quantity">
                            <button class="quantity-btn decrease-btn"><i class="fas fa-minus"></i></button>
                            <input type="number" value="1" min="1" max="10" class="quantity-input">
                            <button class="quantity-btn increase-btn"><i class="fas fa-plus"></i></button>
                        </div>
                        <div class="item-price">
                            <div class="current-price">S/ 34.99</div>
                            <div class="original-price">S/ 44.99</div>
                        </div>
                        <div class="item-actions">
                            <button class="action-btn wishlist-btn" title="Mover a lista de deseos" data-in-wishlist="false">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="action-btn delete-btn" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="coupon-section">
                        <h3><i class="fas fa-ticket-alt"></i> Código de Descuento</h3>
                        <div class="coupon-form">
                            <input type="text" placeholder="Ingresa tu código" class="coupon-input">
                            <button class="apply-coupon">Aplicar</button>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <h3>Resumen del Pedido</h3>
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span>S/ 89.97</span>
                        </div>
                        <div class="summary-item">
                            <span>Descuento</span>
                            <span>-S/ 20.00</span>
                        </div>
                        <div class="summary-item">
                            <span>Envío</span>
                            <span>Gratis</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>S/ 69.97</span>
                        </div>
                        <a href="checkout.php" class="checkout-btn">
                            <i class="fas fa-credit-card"></i> Proceder al Pago
                        </a>
                        <a href="home.php" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i> Seguir Comprando
                        </a>
                    </div>
                </div>
            <?php } else { ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Tu carrito está vacío</h2>
                    <p>Parece que aún no has añadido productos a tu carrito</p>
                    <a href="home.php" class="shop-now-btn">Explorar Productos</a>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <div class="cart-recommendations">
        <h2>Productos que podrían encantarte</h2>
        <div class="recommendations-container">
            <div class="recommendation-item">
                <div class="recommendation-image">
                    <img src="https://via.placeholder.com/150x180/ffe4e1/ffffff" alt="Recomendación 1">
                </div>
                <h4>Anillo Flor Cristal</h4>
                <div class="recommendation-price">S/ 19.99</div>
                <button class="add-to-cart-btn">Añadir</button>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-image">
                    <img src="https://via.placeholder.com/150x180/ffb6c1/ffffff" alt="Recomendación 2">
                </div>
                <h4>Diadema Perlas</h4>
                <div class="recommendation-price">S/ 24.99</div>
                <button class="add-to-cart-btn">Añadir</button>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-image">
                    <img src="https://via.placeholder.com/150x180/ffc0cb/ffffff" alt="Recomendación 3">
                </div>
                <h4>Collar Gargantilla</h4>
                <div class="recommendation-price">S/ 29.99</div>
                <button class="add-to-cart-btn">Añadir</button>
            </div>
            <div class="recommendation-item">
                <div class="recommendation-image">
                    <img src="https://via.placeholder.com/150x180/fadadd/ffffff" alt="Recomendación 4">
                </div>
                <h4>Pendientes Cascada</h4>
                <div class="recommendation-price">S/ 21.99</div>
                <button class="add-to-cart-btn">Añadir</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funcionalidad para botones de cantidad
            const decreaseBtns = document.querySelectorAll('.decrease-btn');
            const increaseBtns = document.querySelectorAll('.increase-btn');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            decreaseBtns.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    const currentValue = parseInt(quantityInputs[index].value);
                    if (currentValue > 1) {
                        quantityInputs[index].value = currentValue - 1;
                    }
                });
            });
            
            increaseBtns.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    const currentValue = parseInt(quantityInputs[index].value);
                    if (currentValue < 10) {
                        quantityInputs[index].value = currentValue + 1;
                    }
                });
            });
            
            // Funcionalidad para botones de eliminar
            const deleteBtns = document.querySelectorAll('.delete-btn');
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const item = this.closest('.cart-item');
                    item.classList.add('removing');
                    setTimeout(() => {
                        item.remove();
                        // Aquí actualizarías el carrito en backend
                    }, 300);
                });
            });
            
            // Funcionalidad para botones de lista de deseos
            const wishlistBtns = document.querySelectorAll('.wishlist-btn');
            wishlistBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const inWishlist = this.getAttribute('data-in-wishlist') === 'true';
                    
                    if (inWishlist) {
                        // Si ya está en la lista de deseos, quitarlo
                        this.setAttribute('data-in-wishlist', 'false');
                        this.classList.remove('in-wishlist');
                        this.setAttribute('title', 'Mover a lista de deseos');
                        
                        // Aquí mostrar un mensaje o notificación
                        showToast('Eliminado de tu lista de deseos');
                    } else {
                        // Si no está en la lista, agregarlo y mostrar animación
                        this.setAttribute('data-in-wishlist', 'true');
                        this.classList.add('in-wishlist');
                        this.setAttribute('title', 'En tu lista de deseos');
                        
                        // Animación de corazón
                        this.querySelector('i').classList.add('heart-pulse');
                        setTimeout(() => {
                            this.querySelector('i').classList.remove('heart-pulse');
                        }, 800);
                        
                        // Aquí mostrar un mensaje o notificación
                        showToast('¡Añadido a tu lista de deseos!');
                    }
                    
                    // Aquí actualizarías la lista de deseos en backend
                });
            });
            
            // Función para mostrar notificaciones toast
            function showToast(message) {
                // Crear el elemento toast si no existe
                let toast = document.getElementById('toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'toast';
                    document.body.appendChild(toast);
                }
                
                // Mostrar mensaje
                toast.textContent = message;
                toast.className = 'show';
                
                // Ocultar después de 3 segundos
                setTimeout(function() { 
                    toast.className = toast.className.replace('show', ''); 
                }, 3000);
            }
        });
    </script>
</body>
</html>

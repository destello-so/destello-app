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
    <link rel="icon" href="assets/img/logo-icon.ico" type="image/x-icon">

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
            <?php
            // Obtener el ID del usuario desde localStorage vía JavaScript
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const userId = localStorage.getItem("userId");
                    if (userId) {
                        // Enviar el ID mediante una petición AJAX
                        fetch("api/get_cart.php?userId=" + userId)
                            .then(response => response.text())
                            .then(data => {
                                document.getElementById("cart-content-container").innerHTML = data;
                            })
                            .catch(error => {
                                console.error("Error al cargar el carrito:", error);
                            });
                    } else {
                        document.getElementById("cart-content-container").innerHTML = 
                            `<div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Tu carrito está vacío</h2>
                                <p>Parece que aún no has iniciado sesión</p>
                                <a href="login.php" class="shop-now-btn">Iniciar Sesión</a>
                            </div>`;
                    }
                });
            </script>';
            ?>
            <div id="cart-content-container">
                <div class="loading-cart">
                    <div class="spinner"></div>
                    <p>Cargando tu carrito...</p>
                </div>
                </div>
        </div>
    </div>
    
    <div class="cart-recommendations">
        <h2>Productos que podrían encantarte</h2>
        <div class="recommendations-container">
            <?php
            // Obtener recomendaciones de productos
            try {
                $stmt = $conn->prepare("
                    SELECT p.id, p.name, p.price, pi.url, pi.alt_text 
                    FROM products p
                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                    ORDER BY RAND()
                    LIMIT 4
                ");
                $stmt->execute();
                
                while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '
                    <div class="recommendation-item" data-product-id="' . $product['id'] . '">
                <div class="recommendation-image">
                            <img src="' . ($product['url'] ?? 'https://via.placeholder.com/150x180/ffe4e1/ffffff') . '" alt="' . htmlspecialchars($product['alt_text'] ?? $product['name']) . '">
                </div>
                        <h4>' . htmlspecialchars($product['name']) . '</h4>
                        <div class="recommendation-price">S/ ' . number_format($product['price'], 2) . '</div>
                <button class="add-to-cart-btn">Añadir</button>
                    </div>';
                }
            } catch (PDOException $e) {
                echo '<p>No se pudieron cargar las recomendaciones: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funcionalidad para botones de añadir recomendaciones al carrito
            const addButtons = document.querySelectorAll('.add-to-cart-btn');
            addButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = localStorage.getItem("userId");
                    if (!userId) {
                        showToast('Debes iniciar sesión para añadir productos');
                        return;
                    }
                    
                    const productId = this.closest('.recommendation-item').getAttribute('data-product-id');
                    
                    fetch('api/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `userId=${userId}&productId=${productId}&quantity=1`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('¡Producto añadido al carrito!');
                            // Recargar el contenido del carrito
                            fetch("api/get_cart.php?userId=" + userId)
                                .then(response => response.text())
                                .then(data => {
                                    document.getElementById("cart-content-container").innerHTML = data;
                                    attachCartEventListeners();
                                });
                    } else {
                            showToast(data.message || 'Error al añadir el producto');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error al añadir el producto');
                    });
                });
            });
            
            // Función para mostrar notificaciones toast
            function showToast(message, type = 'success') {
                // Crear el elemento toast si no existe
                let toast = document.getElementById('toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'toast';
                    document.body.appendChild(toast);
                }
                
                // Aplicar clase según el tipo
                toast.className = `show ${type}`;
                
                // Mostrar mensaje
                toast.textContent = message;
                
                // Ocultar después de 3 segundos
                setTimeout(function() { 
                    toast.className = toast.className.replace('show', ''); 
                }, 3000);
            }
            
            // Función para adjuntar event listeners a los elementos del carrito
            window.attachCartEventListeners = function() {
                console.log("Adjuntando event listeners al carrito...");
                
                // Funcionalidad para botones de cantidad
                const decreaseBtns = document.querySelectorAll('.decrease-btn');
                const increaseBtns = document.querySelectorAll('.increase-btn');
                const quantityInputs = document.querySelectorAll('.quantity-input');
                
                // Log para depuración
                console.log(`Encontrados: ${decreaseBtns.length} botones de disminuir, ${increaseBtns.length} botones de aumentar`);
                
                decreaseBtns.forEach((btn) => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault(); // Prevenir comportamiento por defecto
                        console.log("Botón disminuir clickeado");
                        
                        const item = this.closest('.cart-item');
                        const cartItemId = item.getAttribute('data-cart-item-id');
                        const input = item.querySelector('.quantity-input');
                        const currentValue = parseInt(input.value);
                        
                        if (currentValue > 1) {
                            const newValue = currentValue - 1;
                            input.value = newValue;
                            console.log(`Actualizando cantidad a ${newValue} para item ${cartItemId}`);
                            updateCartItemQuantity(cartItemId, newValue);
                        }
                    });
                });
                
                increaseBtns.forEach((btn) => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault(); // Prevenir comportamiento por defecto
                        console.log("Botón aumentar clickeado");
                        
                        const item = this.closest('.cart-item');
                        const cartItemId = item.getAttribute('data-cart-item-id');
                        const input = item.querySelector('.quantity-input');
                        const currentValue = parseInt(input.value);
                        
                        if (currentValue < 10) {
                            const newValue = currentValue + 1;
                            input.value = newValue;
                            console.log(`Actualizando cantidad a ${newValue} para item ${cartItemId}`);
                            updateCartItemQuantity(cartItemId, newValue);
                        }
                    });
                });
                
                // Actualizar cantidad cuando se cambia manualmente
                quantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        console.log("Input de cantidad cambiado");
                        
                        const item = this.closest('.cart-item');
                        const cartItemId = item.getAttribute('data-cart-item-id');
                        let value = parseInt(this.value);
                        
                        // Validar límites
                        if (isNaN(value) || value < 1) value = 1;
                        if (value > 10) value = 10;
                        
                        this.value = value;
                        console.log(`Actualizando cantidad a ${value} para item ${cartItemId}`);
                        updateCartItemQuantity(cartItemId, value);
                    });
                });
                
                // Funcionalidad para botones de eliminar
                const deleteBtns = document.querySelectorAll('.delete-btn');
                deleteBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        console.log("Botón eliminar clickeado");
                        
                        const item = this.closest('.cart-item');
                        const cartItemId = item.getAttribute('data-cart-item-id');
                        const productName = item.querySelector('.item-details h3').textContent.trim();
                        
                        // Confirmar antes de eliminar (opcional)
                        if (confirm(`¿Estás seguro que deseas eliminar "${productName}" de tu carrito?`)) {
                            console.log(`Eliminando item ${cartItemId} (${productName})`);
                            
                            // Animación de eliminación
                            item.classList.add('removing');
                            
                            // Mostrar mensaje de carga
                            showToast('Eliminando producto...', 'info');
                            
                            // Eliminar de la base de datos
                            fetch('api/remove_from_cart.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `cartItemId=${cartItemId}`
                            })
                            .then(response => {
                                console.log("Respuesta recibida de remove_from_cart.php");
                                return response.json();
                            })
                            .then(data => {
                                console.log("Datos de respuesta:", data);
                                
                                if (data.success) {
                                    // Esperamos a que termine la animación y luego eliminamos el elemento
                                    setTimeout(() => {
                                        item.remove();
                                        showToast('Producto eliminado del carrito', 'success');
                                        
                                        // Actualizar totales
                                        updateCartTotals();
                                        
                                        // Si el carrito quedó vacío, mostramos el mensaje correspondiente
                                        if (data.cartIsEmpty) {
                                            console.log("El carrito quedó vacío");
                                            document.getElementById('cart-content-container').innerHTML = `
                                                <div class="empty-cart">
                                                    <div class="empty-cart-icon">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </div>
                                                    <h2>Tu carrito está vacío</h2>
                                                    <p>Parece que aún no has añadido productos a tu carrito</p>
                                                    <a href="home.php" class="shop-now-btn">Explorar Productos</a>
                                                </div>
                                            `;
                                        } else {
                                            // Verificar si necesitamos actualizar cualquier otro elemento de la UI
                                            const remainingItems = document.querySelectorAll('.cart-item').length;
                                            console.log(`Quedan ${remainingItems} productos en el carrito`);
                                            
                                            // Si visualmente no quedan items pero en la respuesta dice que no está vacío
                                            if (remainingItems === 0 && !data.cartIsEmpty) {
                                                console.log("Discrepancia detectada, recargando carrito");
                                                reloadCart();
                                            }
                                        }
                                    }, 300); // 300ms para que termine la animación
                                } else {
                                    // Si hay error, revertimos la animación
                                    item.classList.remove('removing');
                                    showToast(data.message || 'Error al eliminar el producto', 'error');
                                    console.error("Error al eliminar:", data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error de red:', error);
                                item.classList.remove('removing');
                                showToast('Error de conexión al eliminar', 'error');
                            });
                        }
                    });
                });
                
                // Funcionalidad para botones de lista de deseos
                const wishlistBtns = document.querySelectorAll('.wishlist-btn');
                wishlistBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const inWishlist = this.getAttribute('data-in-wishlist') === 'true';
                        const item = this.closest('.cart-item');
                        const productId = item.getAttribute('data-product-id');
                        const userId = localStorage.getItem("userId");
                        
                        if (!userId) {
                            showToast('Debes iniciar sesión para usar la lista de deseos', 'warning');
                            return;
                        }
                        
                        // Mostrar indicador de carga
                        showToast('Actualizando lista de deseos...', 'info');
                        
                        // Preparar datos para enviar
                        let formData = new FormData();
                        formData.append('userId', userId);
                        formData.append('productId', productId);
                        formData.append('action', 'toggle');
                        
                        // Enviar solicitud para alternar estado en wishlist
                        fetch('api/toggle_wishlist.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const isAdded = data.added;
                                
                                // Actualizar UI
                                this.setAttribute('data-in-wishlist', isAdded ? 'true' : 'false');
                                this.classList.toggle('in-wishlist', isAdded);
                                this.setAttribute('title', isAdded ? 'En tu lista de deseos' : 'Mover a lista de deseos');
                                
                                // Animación de corazón al añadir
                                if (isAdded) {
                                    this.querySelector('i').classList.add('heart-pulse');
                                    setTimeout(() => {
                                        this.querySelector('i').classList.remove('heart-pulse');
                                    }, 800);
                                }
                                
                                // Actualizar contador de wishlist en navbar si existe
                                const navbarWishlistBadge = document.querySelector('.navbar-actions .icon-badge-container:first-child .badge');
                                if (navbarWishlistBadge) {
                                    navbarWishlistBadge.textContent = data.totalItems;
                                    navbarWishlistBadge.classList.add('pulse');
                                    setTimeout(() => {
                                        navbarWishlistBadge.classList.remove('pulse');
                                    }, 1000);
                                }
                                
                                showToast(data.message, 'success');
                            } else {
                                showToast(data.message || 'Error al actualizar la lista de deseos', 'error');
                                console.error("Error en wishlist:", data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error de red:', error);
                            showToast('Error de conexión', 'error');
                        });
                    });
                });
                
                // Funcionalidad para código de descuento
                const couponForm = document.querySelector('.coupon-form');
                if (couponForm) {
                    const applyBtn = couponForm.querySelector('.apply-coupon');
                    applyBtn.addEventListener('click', function() {
                        const couponInput = couponForm.querySelector('.coupon-input');
                        const couponCode = couponInput.value.trim();
                        
                        if (couponCode) {
                            // Aquí implementar la validación del cupón
                            showToast('Código de descuento aplicado');
                            updateCartTotals();
                        } else {
                            showToast('Por favor, ingresa un código de descuento');
                        }
                    });
                }
            };
            
            // Función para actualizar la cantidad en el carrito
            function updateCartItemQuantity(cartItemId, quantity) {
                console.log(`Enviando solicitud AJAX para actualizar ${cartItemId} a cantidad ${quantity}`);
                
                // Mostrar indicador de carga
                showToast('Actualizando cantidad...', 'info');
                
                fetch('api/update_cart_quantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cartItemId=${cartItemId}&quantity=${quantity}`
                })
                .then(response => {
                    console.log('Respuesta recibida', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Datos procesados', data);
                    
                    if (data.success) {
                        // Actualizar el precio total del ítem
                        const item = document.querySelector(`[data-cart-item-id="${cartItemId}"]`);
                        if (item) {
                            // Actualizar el precio unitario × cantidad
                            const priceElement = item.querySelector('.current-price');
                            if (priceElement) {
                                const unitPrice = parseFloat(data.unit_price || priceElement.textContent.replace('S/ ', '').replace(',', '.'));
                                const itemTotal = item.querySelector('.item-total') || document.createElement('div');
                                itemTotal.className = 'item-total';
                                itemTotal.textContent = `Total: S/ ${(unitPrice * quantity).toFixed(2)}`;
                                
                                // Si el elemento no existe, añadirlo
                                if (!item.querySelector('.item-total')) {
                                    const priceContainer = item.querySelector('.item-price');
                                    if (priceContainer) {
                                        priceContainer.appendChild(itemTotal);
                                    }
                                }
                            }
                        }
                        
                        // Actualizar totales del carrito
                        updateCartTotals();
                        showToast('Cantidad actualizada correctamente');
                    } else {
                        showToast(data.message || 'Error al actualizar la cantidad', 'error');
                        console.error('Error en la respuesta', data);
                        
                        // Revertir al valor anterior o recargar
                        reloadCart();
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    showToast('Error al actualizar la cantidad', 'error');
                    reloadCart();
                });
            }
            
            // Función para recargar el carrito completo
            function reloadCart() {
                const userId = localStorage.getItem("userId");
                if (userId) {
                    fetch("api/get_cart.php?userId=" + userId)
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById("cart-content-container").innerHTML = data;
                            attachCartEventListeners();
                        })
                        .catch(error => {
                            console.error("Error al recargar el carrito:", error);
                        });
                }
            }
            
            // Actualizar totales del carrito
            function updateCartTotals() {
                const userId = localStorage.getItem("userId");
                if (!userId) return;
                
                console.log('Actualizando totales del carrito...');
                
                fetch(`api/get_cart_totals.php?userId=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Totales recibidos:', data);
                        
                        if (data.success) {
                            // Si no hay elementos en el carrito, verificamos si necesitamos mostrar el estado de carrito vacío
                            if (data.items_count === 0) {
                                if (!document.querySelector('.empty-cart')) {
                                    document.getElementById('cart-content-container').innerHTML = `
                                        <div class="empty-cart">
                                            <div class="empty-cart-icon">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                            <h2>Tu carrito está vacío</h2>
                                            <p>Parece que aún no has añadido productos a tu carrito</p>
                                            <a href="home.php" class="shop-now-btn">Explorar Productos</a>
                                        </div>
                                    `;
                                }
                                return;
                            }
                            
                            // Actualizar los totales en la interfaz
                            const subtotalElement = document.querySelector('.summary-item:nth-child(1) span:nth-child(2)');
                            const discountElement = document.querySelector('.summary-item:nth-child(2) span:nth-child(2)');
                            const totalElement = document.querySelector('.summary-total span:nth-child(2)');
                            
                            if (subtotalElement) {
                                subtotalElement.textContent = `S/ ${data.subtotal}`;
                                subtotalElement.classList.add('highlight');
                                setTimeout(() => subtotalElement.classList.remove('highlight'), 1000);
                            }
                            
                            if (discountElement) {
                                discountElement.textContent = `${data.discount > 0 ? '-' : ''}S/ ${data.discount}`;
                                discountElement.classList.add('highlight');
                                setTimeout(() => discountElement.classList.remove('highlight'), 1000);
                            }
                            
                            if (totalElement) {
                                totalElement.textContent = `S/ ${data.total}`;
                                totalElement.classList.add('highlight');
                                setTimeout(() => totalElement.classList.remove('highlight'), 1000);
                            }
                            
                            // Actualizar también el contador de productos si existe
                            const itemCountElement = document.querySelector('.summary-item:nth-child(1) span:nth-child(1)');
                            if (itemCountElement && data.items_count !== undefined) {
                                itemCountElement.textContent = `Subtotal (${data.items_count} ${data.items_count == 1 ? 'producto' : 'productos'})`;
                            }
                        } else {
                            console.error('Error al actualizar totales:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Código anterior...
        
        // Script de depuración directo - añade esto al final
        console.log('Inicializando script de depuración del carrito');
        
        // Función para vincular eventos de forma directa
        function initCartActions() {
            console.log('Inicializando acciones del carrito');
            
            // Seleccionar todos los botones directamente
            document.querySelectorAll('.decrease-btn').forEach(btn => {
                btn.onclick = function() {
                    console.log('Botón - clickeado');
                    
                    let item = this.closest('.cart-item');
                    let cartItemId = item.getAttribute('data-cart-item-id');
                    let input = item.querySelector('.quantity-input');
                    let currentValue = parseInt(input.value);
                    
                    if(currentValue > 1) {
                        input.value = currentValue - 1;
                        updateQuantity(cartItemId, currentValue - 1);
                    }
                    
                    return false; // Prevenir comportamiento por defecto
                };
            });
            
            document.querySelectorAll('.increase-btn').forEach(btn => {
                btn.onclick = function() {
                    console.log('Botón + clickeado');
                    
                    let item = this.closest('.cart-item');
                    let cartItemId = item.getAttribute('data-cart-item-id');
                    let input = item.querySelector('.quantity-input');
                    let currentValue = parseInt(input.value);
                    
                    if(currentValue < 10) {
                        input.value = currentValue + 1;
                        updateQuantity(cartItemId, currentValue + 1);
                    }
                    
                    return false; // Prevenir comportamiento por defecto
                };
            });
            
            // Manejar cambios directos en los inputs
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    console.log('Input cambiado manualmente');
                    
                    let item = this.closest('.cart-item');
                    let cartItemId = item.getAttribute('data-cart-item-id');
                    let value = parseInt(this.value);
                    
                    if(isNaN(value) || value < 1) value = 1;
                    if(value > 10) value = 10;
                    
                    this.value = value;
                    updateQuantity(cartItemId, value);
                });
            });
        }
        
        // Función para actualizar la cantidad
        function updateQuantity(cartItemId, quantity) {
            console.log(`Actualizando: cartItemId=${cartItemId}, quantity=${quantity}`);
            
            // Crear un formulario para enviar datos
            let formData = new FormData();
            formData.append('cartItemId', cartItemId);
            formData.append('quantity', quantity);
            
            // Muestra un indicador visual
            document.getElementById('toast') && document.getElementById('toast').remove();
            let toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'show';
            toast.textContent = 'Actualizando...';
            document.body.appendChild(toast);
            
            // Enviar mediante fetch API
            fetch('api/update_cart_quantity.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Respuesta recibida');
                return response.text();
            })
            .then(text => {
                console.log('Texto de respuesta:', text);
                try {
                    let data = JSON.parse(text);
                    console.log('Datos JSON:', data);
                    
                    if(data.success) {
                        toast.textContent = 'Cantidad actualizada';
                        
                        // Actualizar totales manualmente
                        refreshCart();
                    } else {
                        toast.textContent = data.message || 'Error al actualizar';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } catch(e) {
                    console.error('Error al parsear respuesta:', e);
                    toast.textContent = 'Error en la respuesta del servidor';
                    console.log('Respuesta no es JSON válido:', text);
                }
            })
            .catch(error => {
                console.error('Error de red:', error);
                toast.textContent = 'Error de conexión';
            });
            
            // Ocultar toast después de 3 segundos
            setTimeout(() => {
                toast && toast.classList.remove('show');
            }, 3000);
        }
        
        // Función para refrescar el carrito
        window.refreshCart = function() {
            let userId = localStorage.getItem('userId');
            if(!userId) return;
            
            fetch(`api/get_cart.php?userId=${userId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('cart-content-container').innerHTML = html;
                    // Volver a inicializar eventos
                    setTimeout(initCartActions, 100);
                    // También reinicializa los botones de eliminar
                    setTimeout(initializeDeleteButtons, 100);
                    // Reinicializar botones de wishlist
                    setTimeout(initializeWishlistButtons, 100);
                })
                .catch(error => {
                    console.error('Error al refrescar carrito:', error);
                });
        }
        
        // Inicializar los eventos inmediatamente
        initCartActions();
        
        // Asegurarse que el contenido cargado dinámicamente también tenga eventos
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    console.log('DOM modificado, reinicializando eventos');
                    setTimeout(initCartActions, 100);
                }
            });
        });
        
        observer.observe(document.getElementById('cart-content-container'), { 
            childList: true,
            subtree: true 
        });
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Documento cargado, inicializando eliminación de productos");
        
        // Inicializar botones de eliminar
        initializeDeleteButtons();
        
        // Observar cambios en el DOM para reinicializar botones cuando sea necesario
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    console.log("DOM modificado, reinicializando botones de eliminar");
                    setTimeout(initializeDeleteButtons, 100);
                }
            });
        });
        
        // Observar cambios en el contenedor del carrito
        const cartContainer = document.getElementById('cart-content-container');
        if (cartContainer) {
            observer.observe(cartContainer, { 
                childList: true,
                subtree: true 
            });
        }
    });
    </script>
    <script>
    // Función para inicializar los botones de eliminar
    function initializeDeleteButtons() {
        console.log('Inicializando botones de eliminar');
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Botón eliminar clickeado');
                
                const cartItem = this.closest('.cart-item');
                const cartItemId = cartItem.getAttribute('data-cart-item-id');
                
                if (!cartItemId) {
                    console.error('No se encontró el ID del ítem del carrito');
                    return false;
                }
                
                console.log(`Eliminando ítem del carrito: ${cartItemId}`);
                
                // Mostrar indicador visual
                document.getElementById('toast') && document.getElementById('toast').remove();
                let toast = document.createElement('div');
                toast.id = 'toast';
                toast.className = 'show';
                toast.textContent = 'Eliminando producto...';
                document.body.appendChild(toast);
                
                // Crear datos del formulario
                let formData = new FormData();
                formData.append('cartItemId', cartItemId);
                
                // Enviar solicitud al servidor
                fetch('api/remove_from_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    console.log('Respuesta de eliminación:', text);
                    try {
                        const data = JSON.parse(text);
                        
                        if (data.success) {
                            toast.textContent = 'Producto eliminado';
                            
                            // Eliminar el elemento visualmente con animación
                            cartItem.style.opacity = '0';
                            setTimeout(() => {
                                cartItem.style.height = '0';
                                cartItem.style.margin = '0';
                                cartItem.style.padding = '0';
                                
                                setTimeout(() => {
                                    // Actualizar el carrito completo
                                    refreshCart();
                                }, 300);
                            }, 300);
                        } else {
                            toast.textContent = data.message || 'Error al eliminar';
                            console.error('Error al eliminar:', data.message);
                        }
                    } catch (e) {
                        console.error('Error al procesar respuesta:', e);
                        toast.textContent = 'Error en la respuesta del servidor';
                        console.log('Respuesta no es JSON válido:', text);
                    }
                })
                .catch(error => {
                    console.error('Error de red:', error);
                    toast.textContent = 'Error de conexión';
                });
                
                // Ocultar toast después de 3 segundos
                setTimeout(() => {
                    toast && toast.classList.remove('show');
                }, 3000);
                
                return false;
            };
        });
    }
    </script>
    <script>
    // Función para inicializar los botones de wishlist
    function initializeWishlistButtons() {
        console.log('Inicializando botones de wishlist');
        
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Botón wishlist clickeado');
                
                const inWishlist = this.getAttribute('data-in-wishlist') === 'true';
                const item = this.closest('.cart-item');
                const productId = item.getAttribute('data-product-id');
                const userId = localStorage.getItem("userId");
                
                if (!userId) {
                    showToast('Debes iniciar sesión para usar la lista de deseos', 'warning');
                    return false;
                }
                
                // Mostrar indicador de carga
                document.getElementById('toast') && document.getElementById('toast').remove();
                let toast = document.createElement('div');
                toast.id = 'toast';
                toast.className = 'show info';
                toast.textContent = 'Actualizando lista de deseos...';
                document.body.appendChild(toast);
                
                // Preparar datos para enviar
                let formData = new FormData();
                formData.append('userId', userId);
                formData.append('productId', productId);
                formData.append('action', 'toggle');
                
                // Enviar solicitud para alternar estado en wishlist
                fetch('api/toggle_wishlist.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta de wishlist:', data);
                    if (data.success) {
                        const isAdded = data.added;
                        
                        // Actualizar UI
                        this.setAttribute('data-in-wishlist', isAdded ? 'true' : 'false');
                        this.classList.toggle('in-wishlist', isAdded);
                        this.setAttribute('title', isAdded ? 'En tu lista de deseos' : 'Mover a lista de deseos');
                        
                        // Animación de corazón al añadir
                        if (isAdded) {
                            this.querySelector('i').classList.add('heart-pulse');
                            setTimeout(() => {
                                this.querySelector('i').classList.remove('heart-pulse');
                            }, 800);
                        }
                        
                        // Actualizar contador de wishlist en navbar si existe
                        const navbarWishlistBadge = document.querySelector('.navbar-actions .icon-badge-container:first-child .badge');
                        if (navbarWishlistBadge) {
                            navbarWishlistBadge.textContent = data.totalItems;
                            navbarWishlistBadge.classList.add('pulse');
                            setTimeout(() => {
                                navbarWishlistBadge.classList.remove('pulse');
                            }, 1000);
                        }
                        
                        toast.className = 'show success';
                        toast.textContent = data.message;
                    } else {
                        toast.className = 'show error';
                        toast.textContent = data.message || 'Error al actualizar la lista de deseos';
                        console.error("Error en wishlist:", data.message);
                    }
                })
                .catch(error => {
                    console.error('Error de red:', error);
                    toast.className = 'show error';
                    toast.textContent = 'Error de conexión';
                });
                
                // Ocultar toast después de 3 segundos
                setTimeout(() => {
                    toast && toast.classList.remove('show');
                }, 3000);
                
                return false;
            };
        });
    }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Documento cargado, inicializando botones de wishlist");
        
        // Inicializar botones de wishlist
        initializeWishlistButtons();
        
        // Observar cambios en el DOM para reinicializar botones cuando sea necesario
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    console.log("DOM modificado, reinicializando botones de wishlist");
                    setTimeout(initializeWishlistButtons, 100);
                }
            });
        });
        
        // Observar cambios en el contenedor del carrito
        const cartContainer = document.getElementById('cart-content-container');
        if (cartContainer) {
            observer.observe(cartContainer, { 
                childList: true,
                subtree: true 
            });
        }
    });
    </script>
</body>
</html>

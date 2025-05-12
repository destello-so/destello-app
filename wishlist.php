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
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/wishlist.css">
    <link rel="icon" href="assets/img/logo-icon.ico" type="image/x-icon">

    <title>Destello - Lista de Deseos</title>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="wishlist-container">
        <div class="wishlist-header">
            <div class="header-decoration">
                <i class="fas fa-heart"></i>
            </div>
            <h1>Mi Lista de Deseos</h1>
            <p>Descubre los productos que te han enamorado</p>
        </div>
        
        <!-- Filtros -->
        <div class="wishlist-filters" id="wishlistFilters" style="display: none;">
            <div class="filter-group">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchWishlist" placeholder="Buscar en mi lista...">
                </div>
                <div class="sort-container">
                    <select id="sortWishlist">
                        <option value="date-desc">Más recientes</option>
                        <option value="date-asc">Más antiguos</option>
                        <option value="price-asc">Menor precio</option>
                        <option value="price-desc">Mayor precio</option>
                        <option value="name-asc">A-Z</option>
                        <option value="name-desc">Z-A</option>
                    </select>
                </div>
            </div>
            <div class="wishlist-count">
                <span id="itemCount">0</span> productos en tu lista
            </div>
        </div>
        
        <!-- Estado de carga -->
        <div id="loadingState" class="loading-spinner">
            <div class="spinner"></div>
        </div>
        
        <!-- Lista de deseos vacía -->
        <div id="emptyWishlist" class="empty-wishlist" style="display: none;">
            <div class="empty-icon">
                <i class="far fa-heart"></i>
            </div>
            <h2>Tu lista de deseos está vacía</h2>
            <p>Agrega productos a tu lista para guardarlos y comprarlos más tarde.</p>
            <a href="home.php" class="shop-now-btn">
                <i class="fas fa-shopping-bag"></i> Explorar productos
            </a>
        </div>
        
        <!-- Grid de productos -->
        <div id="wishlistGrid" class="wishlist-grid"></div>
    </div>
    
    <!-- Notificación Toast -->
    <div id="toast" class="toast">
        <i class="fas fa-info-circle"></i>
        <span id="toastMessage"></span>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si hay un usuario logueado
            const userId = localStorage.getItem('userId');
            if (!userId) {
                window.location.href = 'login.php?redirect=wishlist';
                return;
            }
            
            // Elementos DOM
            const wishlistGrid = document.getElementById('wishlistGrid');
            const emptyWishlist = document.getElementById('emptyWishlist');
            const loadingState = document.getElementById('loadingState');
            const wishlistFilters = document.getElementById('wishlistFilters');
            const itemCountElement = document.getElementById('itemCount');
            const searchInput = document.getElementById('searchWishlist');
            const sortSelect = document.getElementById('sortWishlist');
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            // Variables globales
            let wishlistItems = [];
            let filteredItems = [];
            
            // Función para mostrar notificaciones
            function showToast(message) {
                toastMessage.textContent = message;
                toast.classList.add('show');
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
            
            // Cargar lista de deseos
            async function loadWishlist() {
                try {
                    // Obtener el ID de la wishlist
                    const wishlistResponse = await fetch(`api/get_wishlist.php?userId=${userId}`);
                    const wishlistData = await wishlistResponse.json();
                    
                    if (!wishlistData.success) {
                        throw new Error(wishlistData.message || 'Error al cargar la lista de deseos');
                    }
                    
                    // Si no hay items, mostrar estado vacío
                    if (wishlistData.items.length === 0) {
                        loadingState.style.display = 'none';
                        emptyWishlist.style.display = 'block';
                        return;
                    }
                    
                    // Guardar los items
                    wishlistItems = wishlistData.items;
                    filteredItems = [...wishlistItems];
                    
                    // Actualizar contador
                    itemCountElement.textContent = wishlistItems.length;
                    
                    // Mostrar filtros
                    wishlistFilters.style.display = 'flex';
                    
                    // Renderizar items
                    renderWishlistItems(wishlistItems);
                    
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error al cargar tu lista de deseos');
                } finally {
                    loadingState.style.display = 'none';
                }
            }
            
            // Renderizar items de la wishlist
            function renderWishlistItems(items) {
                wishlistGrid.innerHTML = '';
                
                if (items.length === 0) {
                    emptyWishlist.style.display = 'block';
                    wishlistFilters.style.display = 'none';
                    return;
                }
                
                items.forEach(item => {
                    const itemElement = createWishlistItemElement(item);
                    wishlistGrid.appendChild(itemElement);
                });
            }
            
            // Crear elemento de item de wishlist
            function createWishlistItemElement(item) {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'wishlist-item';
                itemDiv.dataset.id = item.id;
                
                // Formatear fecha
                const addedDate = new Date(item.added_at);
                const formattedDate = addedDate.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                
                // Determinar estado del stock
                let stockStatus = '';
                let stockClass = '';
                
                if (item.stock_quantity <= 0) {
                    stockStatus = 'Agotado';
                    stockClass = 'out-of-stock';
                } else if (item.stock_quantity < 5) {
                    stockStatus = `¡Solo quedan ${item.stock_quantity}!`;
                    stockClass = 'low-stock';
                } else {
                    stockStatus = 'En stock';
                    stockClass = 'in-stock';
                }
                
                // Crear HTML del item
                itemDiv.innerHTML = `
                    <div class="wishlist-item-image">
                        <img src="${item.image_url || 'https://via.placeholder.com/300x200/ffb6c1/ffffff'}" alt="${item.name}">
                        <div class="wishlist-item-date">Añadido el ${formattedDate}</div>
                    </div>
                    <div class="wishlist-item-content">
                        <h3 class="wishlist-item-title">${item.name}</h3>
                        <p class="wishlist-item-description">${item.description || 'Sin descripción'}</p>
                        <div class="wishlist-item-price">S/ ${parseFloat(item.price).toFixed(2)}</div>
                        <div class="wishlist-item-stock ${stockClass}">${stockStatus}</div>
                        <div class="wishlist-item-actions">
                            <button class="btn btn-primary add-to-cart-btn" ${item.stock_quantity <= 0 ? 'disabled' : ''}>
                                <i class="fas fa-shopping-cart"></i> Añadir al carrito
                            </button>
                            <button class="btn btn-remove remove-wishlist-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                // Eventos de los botones
                const addToCartBtn = itemDiv.querySelector('.add-to-cart-btn');
                const removeBtn = itemDiv.querySelector('.remove-wishlist-btn');
                
                // Añadir al carrito
                addToCartBtn.addEventListener('click', () => {
                    addToCart(item.id, item.name, addToCartBtn);
                });
                
                // Eliminar de la wishlist
                removeBtn.addEventListener('click', () => {
                    removeFromWishlist(item.id, itemDiv);
                });
                
                return itemDiv;
            }
            
            // Filtrar items
            function filterItems() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                if (searchTerm === '') {
                    filteredItems = [...wishlistItems];
                } else {
                    filteredItems = wishlistItems.filter(item => 
                        item.name.toLowerCase().includes(searchTerm) || 
                        (item.description && item.description.toLowerCase().includes(searchTerm))
                    );
                }
                
                sortItems();
            }
            
            // Ordenar items
            function sortItems() {
                const sortValue = sortSelect.value;
                
                switch (sortValue) {
                    case 'date-desc':
                        filteredItems.sort((a, b) => new Date(b.added_at) - new Date(a.added_at));
                        break;
                    case 'date-asc':
                        filteredItems.sort((a, b) => new Date(a.added_at) - new Date(b.added_at));
                        break;
                    case 'price-asc':
                        filteredItems.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
                        break;
                    case 'price-desc':
                        filteredItems.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
                        break;
                    case 'name-asc':
                        filteredItems.sort((a, b) => a.name.localeCompare(b.name));
                        break;
                    case 'name-desc':
                        filteredItems.sort((a, b) => b.name.localeCompare(a.name));
                        break;
                }
                
                renderWishlistItems(filteredItems);
            }
            
            // Añadir al carrito
            async function addToCart(productId, productName, button) {
                try {
                    // Mostrar estado de carga en el botón
                    const originalContent = button.innerHTML;
                    const originalText = button.textContent;
                    
                    button.classList.add('btn-loading');
                    button.innerHTML = `<span class="btn-text">${originalText}</span>`;
                    
                    const formData = new FormData();
                    formData.append('userId', userId);
                    formData.append('productId', productId);
                    formData.append('quantity', 1);
                    
                    const response = await fetch('api/add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    // Quitar estado de carga
                    button.classList.remove('btn-loading');
                    
                    if (data.success) {
                        // Mostrar efecto de éxito
                        button.classList.add('btn-success-pulse');
                        button.innerHTML = `<i class="fas fa-check"></i> Añadido`;
                        
                        // Notificación
                        showToast(`${productName} añadido al carrito`);
                        
                        // Restaurar botón original después de un momento
                        setTimeout(() => {
                            button.classList.remove('btn-success-pulse');
                            button.innerHTML = originalContent;
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Error al añadir al carrito');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    // Restaurar botón a su estado original en caso de error
                    button.classList.remove('btn-loading');
                    button.innerHTML = `<i class="fas fa-shopping-cart"></i> Añadir al carrito`;
                    
                    showToast('Error al añadir el producto al carrito');
                }
            }
            
            // Eliminar de la wishlist
            async function removeFromWishlist(productId, itemElement) {
                try {
                    // Añadir clase de animación
                    itemElement.classList.add('removing');
                    
                    // Crear FormData en lugar de enviar JSON
                    const formData = new FormData();
                    formData.append('userId', userId);
                    formData.append('productId', productId);
                    formData.append('action', 'remove');
                    
                    const response = await fetch('api/toggle_wishlist.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Esperar a que termine la animación
                        setTimeout(() => {
                            // Actualizar arrays de items
                            wishlistItems = wishlistItems.filter(item => item.id !== productId);
                            filterItems();
                            
                            // Actualizar contador
                            itemCountElement.textContent = wishlistItems.length;
                            
                            // Si no quedan items, mostrar estado vacío
                            if (wishlistItems.length === 0) {
                                wishlistFilters.style.display = 'none';
                                emptyWishlist.style.display = 'block';
                            }
                            
                            showToast(data.message);
                        }, 500);
                    } else {
                        itemElement.classList.remove('removing');
                        throw new Error(data.message || 'Error al eliminar de la lista de deseos');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    itemElement.classList.remove('removing');
                    showToast('Error al eliminar el producto');
                }
            }
            
            // Event listeners
            searchInput.addEventListener('input', filterItems);
            sortSelect.addEventListener('change', sortItems);
            
            // Cargar wishlist al iniciar
            loadWishlist();
            
            // Crear API endpoint para obtener la wishlist completa
            // Esta parte debe implementarse en api/get_wishlist.php
        });
    </script>
</body>
</html>



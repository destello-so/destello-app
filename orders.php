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
    <link rel="stylesheet" href="css/orders.css">
    <link rel="icon" href="assets/img/logo-icon.ico" type="image/x-icon">

    <title>Destello - Mis Pedidos</title>   
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="orders-container">
        <div class="orders-header">
            <div class="header-decoration">
                <i class="fas fa-box-open"></i>
            </div>
            <h1>Mis Pedidos</h1>
            <p>Revisa el historial de tus compras realizadas en Destello</p>
        </div>
        
        <!-- Filtros de búsqueda -->
        <div class="orders-filters">
            <div class="filter-group">
                <label for="status-filter">Estado:</label>
                <select id="status-filter">
                    <option value="all">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="processing">En proceso</option>
                    <option value="shipped">Enviado</option>
                    <option value="delivered">Entregado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date-filter">Fecha:</label>
                <select id="date-filter">
                    <option value="all">Todos</option>
                    <option value="today">Hoy</option>
                    <option value="week">Última semana</option>
                    <option value="month">Último mes</option>
                    <option value="6month">Últimos 6 meses</option>
                    <option value="year">Último año</option>
                </select>
            </div>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="order-search" placeholder="Buscar por número de pedido">
            </div>
        </div>
        
        <!-- Estado de carga -->
        <div id="orders-loading" class="loader">
            <div class="spinner"></div>
        </div>
        
        <!-- Lista de pedidos -->
        <div id="orders-list" class="orders-list" style="display: none;"></div>
        
        <!-- Estado vacío -->
        <div id="empty-orders" class="empty-orders" style="display: none;">
            <div class="empty-orders-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <h2>No tienes pedidos aún</h2>
            <p>Explora nuestra tienda y encuentra productos increíbles que te encantarán.</p>
            <a href="home.php" class="shop-now-btn">
                <i class="fas fa-shopping-cart"></i>
                Empezar a comprar
            </a>
        </div>
        
        <!-- Paginación -->
        <div id="pagination" class="pagination" style="display: none;">
            <div class="page-item page-arrow" id="prev-page"><i class="fas fa-chevron-left"></i></div>
            <div class="page-item active">1</div>
            <div class="page-item">2</div>
            <div class="page-item">3</div>
            <div class="page-item page-arrow" id="next-page"><i class="fas fa-chevron-right"></i></div>
        </div>
        
        <!-- Modal de detalles del pedido -->
        <div id="order-detail-modal" class="order-detail-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Detalles del Pedido #<span id="detail-order-number"></span></h2>
                    <button class="modal-close" id="close-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="order-detail-content">
                    <!-- Contenido generado dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para la funcionalidad -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userId = localStorage.getItem('userId');
            const ordersList = document.getElementById('orders-list');
            const emptyOrders = document.getElementById('empty-orders');
            const loading = document.getElementById('orders-loading');
            const pagination = document.getElementById('pagination');
            const modal = document.getElementById('order-detail-modal');
            const closeModal = document.getElementById('close-modal');
            const detailOrderNumber = document.getElementById('detail-order-number');
            const orderDetailContent = document.getElementById('order-detail-content');
            
            const statusFilter = document.getElementById('status-filter');
            const dateFilter = document.getElementById('date-filter');
            const searchInput = document.getElementById('order-search');
            
            // Variables de filtrado
            let currentPage = 1;
            let totalPages = 1;
            let filters = {
                status: 'all',
                date: 'all',
                search: ''
            };
            
            // Iniciar la carga de pedidos
            if (userId) {
                loadOrders();
            } else {
                showEmptyState();
            }
            
            // Escuchar cambios en los filtros
            statusFilter.addEventListener('change', function() {
                filters.status = this.value;
                currentPage = 1;
                loadOrders();
            });
            
            dateFilter.addEventListener('change', function() {
                filters.date = this.value;
                currentPage = 1;
                loadOrders();
            });
            
            searchInput.addEventListener('input', function() {
                filters.search = this.value;
                currentPage = 1;
                loadOrders();
            });
            
            // Paginación
            document.getElementById('prev-page').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadOrders();
                }
            });
            
            document.getElementById('next-page').addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadOrders();
                }
            });
            
            // Cerrar modal
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            function showLoading() {
                loading.style.display = 'flex';
                ordersList.style.display = 'none';
                emptyOrders.style.display = 'none';
                pagination.style.display = 'none';
            }
            
            function showEmptyState() {
                loading.style.display = 'none';
                ordersList.style.display = 'none';
                emptyOrders.style.display = 'block';
                pagination.style.display = 'none';
            }
            
            function loadOrders() {
                showLoading();
                
                // Llamada a la API para cargar pedidos
                fetch(`api/get_orders.php?userId=${userId}&page=${currentPage}&status=${filters.status}&date=${filters.date}&search=${filters.search}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.orders.length === 0) {
                                showEmptyState();
                            } else {
                                renderOrders(data.orders);
                                totalPages = data.totalPages;
                                renderPagination();
                            }
                        } else {
                            showEmptyState();
                            console.error('Error:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showEmptyState();
                    });
            }
            
            function renderOrders(orders) {
                loading.style.display = 'none';
                ordersList.style.display = 'flex';
                emptyOrders.style.display = 'none';
                pagination.style.display = 'flex';
                
                ordersList.innerHTML = '';
                
                orders.forEach(order => {
                    const orderCard = document.createElement('div');
                    orderCard.className = 'order-card';
                    
                    const statusClass = `status-${order.status.toLowerCase()}`;
                    const statusText = getStatusText(order.status);
                    
                    const formattedDate = formatDate(order.created_at);
                    const formattedTotal = formatCurrency(order.total_amount);
                    
                    orderCard.innerHTML = `
                        <div class="order-header">
                            <div class="order-number">Pedido #${order.order_number}</div>
                            <div class="order-date">${formattedDate}</div>
                            <div class="order-status ${statusClass}">${statusText}</div>
                        </div>
                        <div class="order-body">
                            <div class="order-summary">
                                <div class="summary-item">
                                    <div class="summary-label">Total:</div>
                                    <div class="summary-value order-amount">S/ ${formattedTotal}</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Productos:</div>
                                    <div class="summary-value">${order.items_count} artículos</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Dirección:</div>
                                    <div class="summary-value">${order.address.city}, ${order.address.country}</div>
                                </div>
                            </div>
                            <div class="order-toggle" data-order-id="${order.id}">
                                Ver detalles <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="order-details" id="details-${order.id}">
                                <div class="order-products">
                                    <div class="product-list">
                                        ${renderOrderItems(order.items)}
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <button class="btn btn-outline view-detail-btn" data-order-id="${order.id}">
                                        <i class="fas fa-eye"></i> Ver pedido completo
                                    </button>
                                    ${order.status === 'delivered' ? `
                                        <button class="btn btn-primary repeat-order-btn" data-order-id="${order.id}">
                                            <i class="fas fa-redo"></i> Repetir compra
                                        </button>
                                    ` : ''}
                                    ${order.status === 'pending' ? `
                                        <button class="btn btn-outline cancel-order-btn" data-order-id="${order.id}">
                                            <i class="fas fa-times"></i> Cancelar pedido
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    ordersList.appendChild(orderCard);
                });
                
                // Añadir eventos a los botones después de renderizar
                setupEventListeners();
            }
            
            function renderOrderItems(items) {
                let html = '';
                items.forEach(item => {
                    html += `
                        <div class="product-item">
                            <div class="product-image">
                                <img src="${item.image_url || 'https://via.placeholder.com/60x60'}" alt="${item.name}">
                            </div>
                            <div class="product-info">
                                <div class="product-name">${item.name}</div>
                                <div class="product-details">
                                    <div>Cantidad: ${item.quantity}</div>
                                    <div>Precio: S/ ${formatCurrency(item.unit_price)}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                return html;
            }
            
            function setupEventListeners() {
                // Eventos para los toggles
                document.querySelectorAll('.order-toggle').forEach(toggle => {
                    toggle.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        const detailsDiv = document.getElementById(`details-${orderId}`);
                        
                        this.classList.toggle('active');
                        if (detailsDiv.classList.contains('active')) {
                            detailsDiv.classList.remove('active');
                        } else {
                            detailsDiv.classList.add('active');
                        }
                    });
                });
                
                // Eventos para los botones de detalle
                document.querySelectorAll('.view-detail-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        showOrderDetail(orderId);
                    });
                });
                
                // Eventos para los botones de cancelar pedido
                document.querySelectorAll('.cancel-order-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        if (confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
                            cancelOrder(orderId);
                        }
                    });
                });
                
                // Eventos para los botones de repetir compra
                document.querySelectorAll('.repeat-order-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        repeatOrder(orderId);
                    });
                });
            }
            
            function renderPagination() {
                const paginationContainer = document.getElementById('pagination');
                paginationContainer.innerHTML = '';
                
                // Botón Anterior
                const prevBtn = document.createElement('div');
                prevBtn.className = 'page-item page-arrow';
                prevBtn.id = 'prev-page';
                prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevBtn.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        loadOrders();
                    }
                });
                paginationContainer.appendChild(prevBtn);
                
                // Páginas
                const maxPages = Math.min(totalPages, 5);
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(startPage + maxPages - 1, totalPages);
                
                if (endPage - startPage + 1 < maxPages) {
                    startPage = Math.max(1, endPage - maxPages + 1);
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageItem = document.createElement('div');
                    pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
                    pageItem.textContent = i;
                    pageItem.addEventListener('click', function() {
                        if (currentPage !== i) {
                            currentPage = i;
                            loadOrders();
                        }
                    });
                    paginationContainer.appendChild(pageItem);
                }
                
                // Botón Siguiente
                const nextBtn = document.createElement('div');
                nextBtn.className = 'page-item page-arrow';
                nextBtn.id = 'next-page';
                nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextBtn.addEventListener('click', function() {
                    if (currentPage < totalPages) {
                        currentPage++;
                        loadOrders();
                    }
                });
                paginationContainer.appendChild(nextBtn);
                
                // Mostrar u ocultar paginación
                pagination.style.display = totalPages > 1 ? 'flex' : 'none';
            }
            
            function showOrderDetail(orderId) {
                // Obtener detalles del pedido
                fetch(`api/get_order_details.php?orderId=${orderId}&userId=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const order = data.order;
                            
                            detailOrderNumber.textContent = order.order_number;
                            
                            // Formatear fechas y valores
                            const createdDate = formatDate(order.created_at);
                            const updatedDate = formatDate(order.updated_at);
                            const totalAmount = formatCurrency(order.total_amount);
                            
                            // Generar el contenido del modal
                            orderDetailContent.innerHTML = `
                                <div class="order-details-grid">
                                    <div class="detail-section">
                                        <div class="detail-title"><i class="fas fa-info-circle"></i> Información del Pedido</div>
                                        <div class="detail-content">
                                            <div class="detail-item">
                                                <div class="detail-label">Número de Pedido:</div>
                                                <div class="detail-value">#${order.order_number}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Fecha de Pedido:</div>
                                                <div class="detail-value">${createdDate}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Estado:</div>
                                                <div class="detail-value"><span class="order-status status-${order.status.toLowerCase()}">${getStatusText(order.status)}</span></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Última actualización:</div>
                                                <div class="detail-value">${updatedDate}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Total:</div>
                                                <div class="detail-value order-amount">S/ ${totalAmount}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-section">
                                        <div class="detail-title"><i class="fas fa-map-marker-alt"></i> Dirección de Envío</div>
                                        <div class="detail-content">
                                            <div class="detail-item">
                                                <div class="detail-label">Destinatario:</div>
                                                <div class="detail-value">${order.user.first_name} ${order.user.last_name}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Dirección:</div>
                                                <div class="detail-value">${order.address.street}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Ciudad/Estado:</div>
                                                <div class="detail-value">${order.address.city}, ${order.address.state}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">País:</div>
                                                <div class="detail-value">${order.address.country}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Código Postal:</div>
                                                <div class="detail-value">${order.address.zip_code}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-section">
                                    <div class="detail-title"><i class="fas fa-shopping-bag"></i> Productos Comprados</div>
                                    <div class="product-list">
                                        ${renderDetailedOrderItems(order.items)}
                                    </div>
                                </div>
                                
                                ${order.shipments && order.shipments.length > 0 ? `
                                <div class="detail-section">
                                    <div class="detail-title"><i class="fas fa-truck"></i> Información de Envío</div>
                                    <div class="detail-content">
                                        <div class="detail-item">
                                            <div class="detail-label">Transporte:</div>
                                            <div class="detail-value">${order.shipments[0].carrier}</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Número de Seguimiento:</div>
                                            <div class="detail-value">${order.shipments[0].tracking_number || 'No disponible'}</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Estado del Envío:</div>
                                            <div class="detail-value">${order.shipments[0].status}</div>
                                        </div>
                                        ${order.shipments[0].shipped_at ? `
                                        <div class="detail-item">
                                            <div class="detail-label">Fecha de Envío:</div>
                                            <div class="detail-value">${formatDate(order.shipments[0].shipped_at)}</div>
                                        </div>
                                        ` : ''}
                                        ${order.shipments[0].delivered_at ? `
                                        <div class="detail-item">
                                            <div class="detail-label">Fecha de Entrega:</div>
                                            <div class="detail-value">${formatDate(order.shipments[0].delivered_at)}</div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                                ` : ''}
                                
                                <div class="order-timeline">
                                    <div class="timeline-track"></div>
                                    ${renderOrderTimeline(order)}
                                </div>
                                
                                <div class="order-actions" style="justify-content: center; margin-top: 30px;">
                                    ${order.status === 'delivered' ? `
                                        <button class="btn btn-primary repeat-order-btn" data-order-id="${order.id}">
                                            <i class="fas fa-redo"></i> Repetir esta compra
                                        </button>
                                    ` : ''}
                                    ${order.status === 'pending' ? `
                                        <button class="btn btn-outline cancel-order-btn" data-order-id="${order.id}">
                                            <i class="fas fa-times"></i> Cancelar pedido
                                        </button>
                                    ` : ''}
                                </div>
                            `;
                            
                            // Mostrar el modal
                            modal.style.display = 'flex';
                            
                            // Añadir eventos a los botones dentro del modal
                            setupModalEventListeners();
                        } else {
                            alert('No se pudo cargar la información del pedido.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cargar los detalles del pedido.');
                    });
            }
            
            function renderDetailedOrderItems(items) {
                let html = '';
                items.forEach(item => {
                    const itemTotal = item.unit_price * item.quantity;
                    html += `
                        <div class="product-item">
                            <div class="product-image">
                                <img src="${item.image_url || 'https://via.placeholder.com/60x60'}" alt="${item.name}">
                            </div>
                            <div class="product-info">
                                <div class="product-name">${item.name}</div>
                                <div class="product-details">
                                    <div>Cantidad: ${item.quantity}</div>
                                    <div>Precio unitario: S/ ${formatCurrency(item.unit_price)}</div>
                                    <div>Total: S/ ${formatCurrency(itemTotal)}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                return html;
            }
            
            function renderOrderTimeline(order) {
                // Crear línea de tiempo basada en el estado del pedido
                let events = [];
                
                // Siempre añadir el evento de creación
                events.push({
                    title: 'Pedido Realizado',
                    icon: 'fa-shopping-cart',
                    time: order.created_at,
                    description: 'Tu pedido ha sido recibido y está siendo procesado.'
                });
                
                // Añadir eventos según el estado
                if (order.status === 'processing' || order.status === 'shipped' || order.status === 'delivered') {
                    events.push({
                        title: 'Pedido en Proceso',
                        icon: 'fa-cog',
                        time: null,
                        description: 'Estamos preparando tus productos para envío.'
                    });
                }
                
                if (order.status === 'shipped' || order.status === 'delivered') {
                    const shipDate = order.shipments && order.shipments.length > 0 ? order.shipments[0].shipped_at : null;
                    events.push({
                        title: 'Pedido Enviado',
                        icon: 'fa-truck',
                        time: shipDate,
                        description: `Tu pedido ha sido enviado a través de ${order.shipments[0].carrier}.`
                    });
                }
                
                if (order.status === 'delivered') {
                    const deliveryDate = order.shipments && order.shipments.length > 0 ? order.shipments[0].delivered_at : null;
                    events.push({
                        title: 'Pedido Entregado',
                        icon: 'fa-check-circle',
                        time: deliveryDate,
                        description: 'Tu pedido ha sido entregado satisfactoriamente.'
                    });
                }
                
                if (order.status === 'cancelled') {
                    events.push({
                        title: 'Pedido Cancelado',
                        icon: 'fa-times-circle',
                        time: order.updated_at,
                        description: 'Este pedido ha sido cancelado.'
                    });
                }
                
                // Renderizar eventos
                let html = '';
                events.forEach(event => {
                    html += `
                        <div class="timeline-event">
                            <div class="timeline-icon">
                                <i class="fas ${event.icon}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">${event.title}</div>
                                ${event.time ? `<div class="timeline-time">${formatDate(event.time)}</div>` : ''}
                                <div class="timeline-description">${event.description}</div>
                            </div>
                        </div>
                    `;
                });
                
                return html;
            }
            
            function setupModalEventListeners() {
                // Eventos para botones dentro del modal
                document.querySelectorAll('.modal .repeat-order-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        repeatOrder(orderId);
                        modal.style.display = 'none';
                    });
                });
                
                document.querySelectorAll('.modal .cancel-order-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        if (confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
                            cancelOrder(orderId);
                            modal.style.display = 'none';
                        }
                    });
                });
            }
            
            function cancelOrder(orderId) {
                fetch('api/cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userId: userId,
                        orderId: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido cancelado con éxito');
                        loadOrders(); // Recargar la lista de pedidos
                    } else {
                        alert(data.message || 'No se pudo cancelar el pedido');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cancelar el pedido');
                });
            }
            
            function repeatOrder(orderId) {
                fetch('api/repeat_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userId: userId,
                        orderId: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Productos añadidos al carrito');
                        window.location.href = 'cart.php'; // Redirigir al carrito
                    } else {
                        alert(data.message || 'No se pudo repetir el pedido');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al repetir el pedido');
                });
            }
            
            // Funciones auxiliares
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                const date = new Date(dateString);
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            function formatCurrency(amount) {
                return parseFloat(amount).toFixed(2);
            }
            
            function getStatusText(status) {
                const statusMap = {
                    'pending': 'Pendiente',
                    'processing': 'En proceso',
                    'shipped': 'Enviado',
                    'delivered': 'Entregado',
                    'cancelled': 'Cancelado'
                };
                
                return statusMap[status] || status;
            }
        });
    </script>
</body>
</html>

// Navigation
document.querySelectorAll('.menu-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active menu
        document.querySelectorAll('.menu-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        
        // Show corresponding section
        const section = this.getAttribute('data-section');
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
        document.getElementById(section).classList.add('active');
        
        // Load data for the section
        loadSectionData(section);
    });
});

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadLowStock();
    
    // Add search and filter listeners
    const searchInput = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }

    // Add product form submit handler (inline form)
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('productName').value.trim();
            const category = document.getElementById('productCategory').value.trim();
            const price = document.getElementById('productPrice').value.trim();
            const stock = document.getElementById('productStock').value.trim();
            const image = document.getElementById('productImage').value.trim();

            if (!name || !category || !price) {
                alert('Please enter product name, category and price.');
                return;
            }

            const submitBtn = document.getElementById('addProductSubmit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            const payload = {
                name: name,
                category: category,
                price: parseFloat(price),
                stock_quantity: stock ? parseInt(stock, 10) : 0,
                image: image || null
            };

            fetch('api/add_product_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addProductForm.reset();
                    toggleAddProductForm();
                    loadProducts();
                    loadDashboardStats();
                    showNotification('Product added successfully!', 'success');
                } else {
                    throw new Error(data.error || 'Failed to add product');
                }
            })
            .catch(error => {
                console.error('Error adding product:', error);
                alert('Error: ' + error.message);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

let allProducts = []; // Store all products for filtering

function filterProducts() {
    const searchTerm = document.getElementById('productSearch').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    
    const filtered = allProducts.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                            product.category.toLowerCase().includes(searchTerm);
        const matchesCategory = !category || product.category === category;
        return matchesSearch && matchesCategory;
    });
    
    displayProducts(filtered);
}

function loadSectionData(section) {
    switch(section) {
        case 'overview':
            loadDashboardStats();
            loadLowStock();
            break;
        case 'products':
            loadProducts();
            break;
        case 'suppliers':
            loadSuppliers();
            break;
        case 'customers':
            loadCustomers();
            break;
        case 'orders':
            loadOrders();
            break;
    }
}

// Dashboard Stats
function loadDashboardStats() {
    fetch('api/get_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalProducts').textContent = data.products || 0;
            document.getElementById('totalSuppliers').textContent = data.suppliers || 0;
            document.getElementById('totalCustomers').textContent = data.customers || 0;
            document.getElementById('totalOrders').textContent = data.orders || 0;
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadLowStock() {
    fetch('api/get_low_stock.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#lowStockTable tbody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No low stock items</td></tr>';
                return;
            }
            
            data.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.category}</td>
                        <td><span class="badge badge-danger">${item.stock_quantity}</span></td>
                        <td>${item.min_stock_level}</td>
                        <td><button class="btn btn-warning btn-sm" onclick="updateStock(${item.id})">Update Stock</button></td>
                    </tr>
                `;
            });
        });
}

// Products
function loadProducts() {
    const grid = document.getElementById('productsGrid');
    if (!grid) {
        console.error('Products grid element not found');
        return;
    }
    
    grid.innerHTML = '<p style="text-align:center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading products...</p>';
    
    fetch('api/get_products_admin.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Products loaded:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            allProducts = data; // Store for filtering
            displayProducts(data);
        })
        .catch(error => {
            console.error('Error loading products:', error);
            grid.innerHTML = `
                <div style="text-align:center; grid-column: 1/-1; padding: 40px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #e74c3c; margin-bottom: 15px;"></i>
                    <h3 style="color: #e74c3c;">Error loading products</h3>
                    <p style="color: #7f8c8d;">${error.message}</p>
                    <button onclick="loadProducts()" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            `;
        });
}

function displayProducts(data) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = '';
    
    if (data.length === 0) {
        grid.innerHTML = '<p style="text-align:center; grid-column: 1/-1; padding: 40px; color: #7f8c8d;">No products found.</p>';
        return;
    }
    
    data.forEach(product => {
        const stockClass = product.stock_quantity < product.min_stock_level ? 'badge-danger' : 'badge-success';
        const stockStatus = product.stock_quantity < product.min_stock_level ? 'Low Stock' : 'In Stock';
        const minStock = product.min_stock_level || 10;
        
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='https://via.placeholder.com/200?text=No+Image'">
            <div class="product-info">
                <span class="product-id">ID: ${product.id}</span>
                <div class="product-name">${product.name}</div>
                <div style="margin-bottom: 10px;">
                    <span class="product-category">${product.category}</span>
                </div>
                
                <div class="product-price">RS ${parseFloat(product.price).toFixed(2)}</div>
                
                <div class="product-details">
                    <div class="product-detail-row">
                        <span class="product-detail-label"><i class="fas fa-boxes"></i> Current Stock</span>
                        <span class="product-detail-value">${product.stock_quantity || 0} units</span>
                    </div>
                    <div class="product-detail-row">
                        <span class="product-detail-label"><i class="fas fa-exclamation-triangle"></i> Min Stock Level</span>
                        <span class="product-detail-value">${minStock} units</span>
                    </div>
                    <div class="product-detail-row">
                        <span class="product-detail-label"><i class="fas fa-chart-line"></i> Stock Status</span>
                        <span class="badge ${stockClass}">${stockStatus}</span>
                    </div>
                </div>
                
                <div class="product-supplier">
                    <i class="fas fa-truck"></i>
                    <span><strong>Supplier:</strong> ${product.supplier_name || 'Not assigned'}</span>
                </div>
                
                <div class="product-actions">
                    <button class="btn btn-primary btn-sm btn-view" onclick="viewProductDetails(${product.id})" title="View Full Details">
                        <i class="fas fa-eye"></i> View Full Details
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="editProduct(${product.id})" title="Edit Product">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="updateStock(${product.id})" title="Update Stock">
                        <i class="fas fa-boxes"></i> Stock
                    </button>
                    <button class="btn btn-success btn-sm" onclick="assignSupplier(${product.id})" title="Assign Supplier">
                        <i class="fas fa-truck"></i> Supplier
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteProduct(${product.id})" title="Delete Product">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(productCard);
    });
}

// Suppliers
function loadSuppliers() {
    fetch('api/get_suppliers.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#suppliersTable tbody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 40px; color: #7f8c8d;">No suppliers found. Add your first supplier!</td></tr>';
                return;
            }
            
            data.forEach(supplier => {
                tbody.innerHTML += `
                    <tr>
                        <td>${supplier.id}</td>
                        <td>${supplier.name || '-'}</td>
                        <td>${supplier.contact_person || '-'}</td>
                        <td>${supplier.email || '-'}</td>
                        <td>${supplier.phone || '-'}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editSupplier(${supplier.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteSupplier(${supplier.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error loading suppliers:', error);
            const tbody = document.querySelector('#suppliersTable tbody');
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 40px; color: #e74c3c;">Error loading suppliers. Please try again.</td></tr>';
        });
}

// Customers
function loadCustomers() {
    fetch('api/get_customers.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#customersTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(customer => {
                tbody.innerHTML += `
                    <tr>
                        <td>${customer.id}</td>
                        <td>${customer.username}</td>
                        <td>${customer.email}</td>
                        <td>${new Date(customer.created_at).toLocaleDateString()}</td>
                        <td>${customer.order_count || 0}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="viewCustomerOrders(${customer.id})"><i class="fas fa-eye"></i> Orders</button>
                        </td>
                    </tr>
                `;
            });
        });
}

// Orders
function loadOrders() {
    fetch('api/get_orders_admin.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#ordersTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(order => {
                let statusClass = 'badge-warning';
                if (order.status === 'completed') statusClass = 'badge-success';
                if (order.status === 'cancelled') statusClass = 'badge-danger';
                
                tbody.innerHTML += `
                    <tr>
                        <td>#${order.id}</td>
                        <td>${order.username}</td>
                        <td>RS ${order.total_amount}</td>
                        <td><span class="badge ${statusClass}">${order.status}</span></td>
                        <td>${new Date(order.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="viewOrderDetails(${order.id})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-success btn-sm" onclick="updateOrderStatus(${order.id})"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                `;
            });
        });
}

// Toggle Add Product inline form instead of browser prompts
function toggleAddProductForm() {
    const container = document.getElementById('addProductContainer');
    if (!container) return;

    const isHidden = container.style.display === 'none' || container.style.display === '';
    container.style.display = isHidden ? 'block' : 'none';

    if (isHidden) {
        const nameInput = document.getElementById('productName');
        if (nameInput) {
            nameInput.focus();
        }
    }
}

function showAddSupplierModal() {
    document.getElementById('supplierModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Clear form
    document.getElementById('supplierForm').reset();
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('supplierName').focus();
    }, 300);
}

function closeSupplierModal() {
    document.getElementById('supplierModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function saveSupplier() {
    const name = document.getElementById('supplierName').value.trim();
    
    if (!name) {
        alert('Please enter supplier name');
        document.getElementById('supplierName').focus();
        return;
    }
    
    const supplierData = {
        name: name,
        company_name: document.getElementById('companyName').value.trim(),
        contact_person: document.getElementById('contactPerson').value.trim(),
        email: document.getElementById('supplierEmail').value.trim(),
        phone: document.getElementById('supplierPhone').value.trim(),
        address: document.getElementById('supplierAddress').value.trim()
    };
    
    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('api/add_supplier.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(supplierData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success animation
            saveBtn.innerHTML = '<i class="fas fa-check"></i> Saved!';
            saveBtn.style.background = '#27ae60';
            
            setTimeout(() => {
                closeSupplierModal();
                loadSuppliers();
                loadDashboardStats();
                
                // Reset button
                saveBtn.innerHTML = originalText;
                saveBtn.style.background = '';
                saveBtn.disabled = false;
                
                // Show success notification
                showNotification('Supplier added successfully!', 'success');
            }, 1000);
        } else {
            throw new Error(data.error || 'Failed to add supplier');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        alert('Error: ' + error.message);
    });
}

// Close modal when clicking overlay
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('supplierModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeSupplierModal();
            }
        });
    }
});

// Add notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 10002;
        font-weight: 600;
        animation: slideInRight 0.3s ease-out;
    `;
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function updateStock(productId) {
    const quantity = prompt('Enter new stock quantity:');
    if (quantity !== null) {
        fetch('api/update_stock.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: productId, stock_quantity: quantity})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Stock updated!');
                loadProducts();
                loadLowStock();
            }
        });
    }
}

function assignSupplier(productId) {
    fetch('api/get_suppliers.php')
        .then(response => response.json())
        .then(suppliers => {
            let options = suppliers.map(s => `${s.id}. ${s.name}`).join('\n');
            const supplierId = prompt('Select Supplier:\n' + options + '\n\nEnter Supplier ID:');
            
            if (supplierId) {
                fetch('api/assign_supplier.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({product_id: productId, supplier_id: supplierId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Supplier assigned!');
                        loadProducts();
                    }
                });
            }
        });
}

function deleteProduct(id) {
    if (confirm('Delete this product?')) {
        fetch('api/delete_product.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product deleted!');
                loadProducts();
                loadDashboardStats();
            }
        });
    }
}

function editProduct(id) {
    // Fetch product details
    fetch('api/get_products_admin.php')
        .then(response => response.json())
        .then(products => {
            const product = products.find(p => p.id == id);
            if (product) {
                const name = prompt('Product Name:', product.name);
                if (name === null) return;
                
                const category = prompt('Category:', product.category);
                if (category === null) return;
                
                const price = prompt('Price:', product.price);
                if (price === null) return;
                
                const stock = prompt('Stock Quantity:', product.stock_quantity || 0);
                if (stock === null) return;
                
                const image = prompt('Image URL:', product.image);
                if (image === null) return;
                
                // Update product
                fetch('api/update_product.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        id: id,
                        name: name,
                        category: category,
                        price: price,
                        stock_quantity: stock,
                        image: image
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Product updated successfully!');
                        loadProducts();
                    } else {
                        alert('Error updating product: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update product');
                });
            }
        });
}

function deleteSupplier(id) {
    if (confirm('Delete this supplier?')) {
        fetch('api/delete_supplier.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Supplier deleted!');
                loadSuppliers();
            }
        });
    }
}

function viewOrderDetails(orderId) {
    window.open('order_details.php?id=' + orderId, '_blank');
}

function viewCustomerOrders(customerId) {
    window.open('customer_orders.php?id=' + customerId, '_blank');
}

let currentOrderId = null;
let selectedStatusValue = '';

function updateOrderStatus(orderId) {
    currentOrderId = orderId;
    selectedStatusValue = '';
    
    // Reset UI
    document.querySelectorAll('.status-option').forEach(btn => {
        btn.classList.remove('selected');
    });
    document.getElementById('statusInput').value = '';
    
    // Show modal
    const modal = document.getElementById('statusModal');
    modal.classList.add('show');
    
    // Focus on input
    setTimeout(() => {
        document.getElementById('statusInput').focus();
    }, 100);
}

function selectStatus(status) {
    selectedStatusValue = status;
    
    // Update UI
    document.querySelectorAll('.status-option').forEach(btn => {
        btn.classList.remove('selected');
        if (btn.dataset.status === status) {
            btn.classList.add('selected');
        }
    });
    
    // Clear input
    document.getElementById('statusInput').value = '';
}

function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    modal.classList.remove('show');
    currentOrderId = null;
    selectedStatusValue = '';
}

function confirmStatusUpdate() {
    if (!currentOrderId) return;
    
    // Get status from input or selected button
    let status = selectedStatusValue;
    if (!status) {
        status = document.getElementById('statusInput').value.trim();
    }
    
    if (!status) {
        alert('Please select or enter a status');
        return;
    }
    
    const normalizedStatus = status.toLowerCase();
    const confirmBtn = document.querySelector('.btn-confirm');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    console.log('Updating order', currentOrderId, 'to status:', normalizedStatus);
    
    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({order_id: currentOrderId, status: normalizedStatus})
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Show success message
            showStatusMessage('Order status updated to "' + normalizedStatus + '"! Customer will receive a notification.', 'success');
            closeStatusModal();
            loadOrders();
        } else {
            showStatusMessage('Error: ' + (data.error || 'Failed to update order status'), 'error');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Update Status';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showStatusMessage('Error updating order status. Please check console for details.', 'error');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Update Status';
    });
}

function showStatusMessage(message, type) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#06d6a0' : '#d62839'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10001;
        animation: slideInRight 0.3s ease;
        font-weight: 500;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('statusModal');
    if (e.target === modal) {
        closeStatusModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
    }
});

// Allow Enter key in input to confirm (using event delegation)
document.addEventListener('keypress', function(e) {
    if (e.target.id === 'statusInput' && e.key === 'Enter') {
        e.preventDefault();
        confirmStatusUpdate();
    }
});

// Product Details Modal
function viewProductDetails(productId) {
    const product = allProducts.find(p => p.id == productId);
    if (!product) return;
    
    const stockClass = product.stock_quantity < product.min_stock_level ? 'badge-danger' : 'badge-success';
    const stockStatus = product.stock_quantity < product.min_stock_level ? 'Low Stock' : 'In Stock';
    const minStock = product.min_stock_level || 10;
    
    const modalContent = `
        <h2 style="color: #2a3f54; margin-bottom: 20px; border-bottom: 3px solid #f8b739; padding-bottom: 10px;">
            <i class="fas fa-box"></i> Product Details
        </h2>
        
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; margin-bottom: 20px;">
            <div>
                <img src="${product.image}" alt="${product.name}" 
                     style="width: 100%; border-radius: 10px; border: 2px solid #f8b739;"
                     onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
            </div>
            
            <div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h3 style="color: #2a3f54; margin-bottom: 15px;">${product.name}</h3>
                    <span style="background: #f8b739; color: #2a3f54; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                        ${product.category}
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div style="color: #6c757d; font-size: 12px; margin-bottom: 5px;">PRODUCT ID</div>
                        <div style="color: #2a3f54; font-size: 18px; font-weight: bold;">#${product.id}</div>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div style="color: #6c757d; font-size: 12px; margin-bottom: 5px;">PRICE</div>
                        <div style="color: #2a3f54; font-size: 18px; font-weight: bold;">RS ${parseFloat(product.price).toFixed(2)}</div>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div style="color: #6c757d; font-size: 12px; margin-bottom: 5px;">CURRENT STOCK</div>
                        <div style="color: #2a3f54; font-size: 18px; font-weight: bold;">${product.stock_quantity || 0} units</div>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div style="color: #6c757d; font-size: 12px; margin-bottom: 5px;">MIN STOCK LEVEL</div>
                        <div style="color: #2a3f54; font-size: 18px; font-weight: bold;">${minStock} units</div>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div style="color: #6c757d; font-size: 12px; margin-bottom: 5px;">STOCK STATUS</div>
                        <span class="badge ${stockClass}" style="font-size: 14px;">${stockStatus}</span>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div style="color: #6c757d; font-size: 12px; margin-bottom: 5px;">SUPPLIER</div>
                        <div style="color: #2a3f54; font-size: 14px; font-weight: 600;">
                            <i class="fas fa-truck" style="color: #f8b739;"></i> ${product.supplier_name || 'Not assigned'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <h4 style="color: #2a3f54; margin-bottom: 10px;"><i class="fas fa-link"></i> Image URL</h4>
            <div style="color: #6c757d; word-break: break-all; font-size: 13px;">${product.image}</div>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button onclick="editProduct(${product.id}); closeProductModal();" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Product
            </button>
            <button onclick="updateStock(${product.id}); closeProductModal();" class="btn btn-warning">
                <i class="fas fa-boxes"></i> Update Stock
            </button>
            <button onclick="assignSupplier(${product.id}); closeProductModal();" class="btn btn-success">
                <i class="fas fa-truck"></i> Assign Supplier
            </button>
            <button onclick="closeProductModal();" class="btn btn-secondary" style="background: #6c757d; color: white;">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    `;
    
    document.getElementById('productModalContent').innerHTML = modalContent;
    document.getElementById('productModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

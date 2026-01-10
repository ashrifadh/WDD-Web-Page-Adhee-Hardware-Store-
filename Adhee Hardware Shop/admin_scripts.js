document.addEventListener("DOMContentLoaded", () => {
    const addProductForm = document.getElementById('addProductForm');
    const productTableBody = document.getElementById('productTable').getElementsByTagName('tbody')[0];

    // Function to show notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Function to set loading state
    function setLoading(button, isLoading) {
        if (isLoading) {
            button.classList.add('loading');
            button.disabled = true;
        } else {
            button.classList.remove('loading');
            button.disabled = false;
        }
    }

    // Function to get category badge class
    function getCategoryBadgeClass(category) {
        return `category-${category.replace(/\s+/g, '')}`;
    }

    // Function to fetch and display products
    async function loadProducts() {
        try {
            const response = await fetch('fetch_products.php');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            productTableBody.innerHTML = '';
            
            if (data.length === 0) {
                productTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">No products found</td>
                    </tr>
                `;
                return;
            }
            
            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.name}</td>
                    <td><span class="category-badge ${getCategoryBadgeClass(product.category)}">${product.category}</span></td>
                    <td>RS ${parseFloat(product.price).toFixed(2)}</td>
                    <td>
                        <img src="${product.image}" alt="${product.name}" class="product-img" 
                             onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                    </td>
                    <td class="actions-cell">
                        <button class="btn btn-warning btn-sm edit-btn" data-id="${product.id}" 
                                data-name="${product.name}" data-category="${product.category}" 
                                data-price="${product.price}" data-image="${product.image}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm remove-btn" data-id="${product.id}">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </td>
                `;
                productTableBody.appendChild(row);
            });

            // Add event listeners for remove buttons
            document.querySelectorAll('.remove-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = e.currentTarget.dataset.id;
                    if (confirm('Are you sure you want to remove this product?')) {
                        removeProduct(productId);
                    }
                });
            });

            // Add event listeners for edit buttons
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const btn = e.currentTarget;
                    editProduct(btn.dataset.id, btn.dataset.name, btn.dataset.category, 
                               btn.dataset.price, btn.dataset.image);
                });
            });
            
        } catch (error) {
            console.error('Error fetching products:', error);
            showNotification('Failed to load products', 'error');
        }
    }

    // Function to remove product
    async function removeProduct(productId) {
        const button = document.querySelector(`.remove-btn[data-id="${productId}"]`);
        setLoading(button, true);
        
        try {
            const response = await fetch('remove_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: productId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Product removed successfully');
                loadProducts();
            } else {
                throw new Error(result.message || 'Failed to remove product');
            }
        } catch (error) {
            console.error('Error removing product:', error);
            showNotification(error.message, 'error');
        } finally {
            setLoading(button, false);
        }
    }

    // Function to populate form for editing
    function editProduct(id, name, category, price, image) {
        document.getElementById('productId').value = id;
        document.getElementById('productName').value = name;
        document.getElementById('productCategory').value = category;
        document.getElementById('productPrice').value = price;
        document.getElementById('productImage').value = image;
        
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Product';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Product';
        document.getElementById('cancelBtn').style.display = 'inline-block';
        
        // Scroll to form
        document.querySelector('.add-product-section').scrollIntoView({ behavior: 'smooth' });
    }

    // Function to reset form
    function resetForm() {
        document.getElementById('productId').value = '';
        addProductForm.reset();
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Add New Product';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Add Product';
        document.getElementById('cancelBtn').style.display = 'none';
    }

    // Cancel button handler
    document.getElementById('cancelBtn').addEventListener('click', resetForm);

    // Event listener to handle product addition
    addProductForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitButton = addProductForm.querySelector('button[type="submit"]');
        setLoading(submitButton, true);
        
        const productId = document.getElementById('productId').value;
        const name = document.getElementById('productName').value.trim();
        const category = document.getElementById('productCategory').value;
        const price = document.getElementById('productPrice').value;
        const image = document.getElementById('productImage').value.trim();

        const isEdit = productId !== '';
        const url = isEdit ? 'update_product.php' : 'add_product.php';
        const data = isEdit ? { id: productId, name, category, price, image } : { name, category, price, image };

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(isEdit ? 'Product updated successfully' : 'Product added successfully');
                resetForm();
                loadProducts();
            } else {
                throw new Error(result.message || `Failed to ${isEdit ? 'update' : 'add'} product`);
            }
        } catch (error) {
            console.error(`Error ${isEdit ? 'updating' : 'adding'} product:`, error);
            showNotification(error.message, 'error');
        } finally {
            setLoading(submitButton, false);
        }
    });

    // Initial load of products
    loadProducts();
});
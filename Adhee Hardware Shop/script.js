// Global products array
let products = [];

// DOM Elements
const productContainer = document.getElementById('productContainer');
const categoryContainer = document.querySelector('.category-container'); // Updated selector
const cartCount = document.querySelector('.cart-count');
const cartIcon = document.querySelector('.cart-icon');
const cartModal = document.getElementById('cartModal');
const cartItems = document.getElementById('cartItems');
const cartTotal = document.getElementById('cartTotal');
const closeModal = document.querySelector('.close-modal');
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

// Cart state
let cart = [];
let currentCategory = 'all';
let searchTerm = '';

// Categories data with icons
const categories = [
    { id: "all", name: "All Products", description: "Browse our complete inventory", icon: "fas fa-box-open" },
    { id: "hand-tools", name: "Hand Tools", description: "Hammers, screwdrivers, wrenches", icon: "fas fa-tools" },
    { id: "power-tools", name: "Power Tools", description: "Drills, saws, sanders", icon: "fas fa-bolt" },
    { id: "measuring-tools", name: "Measuring Tools", description: "Tapes, levels, lasers", icon: "fas fa-ruler-combined" },
    { id: "safety-equipment", name: "Safety Equipment", description: "Glasses, gloves, masks", icon: "fas fa-hard-hat" },
    { id: "plumbing", name: "Plumbing", description: "Pipes, fittings, valves", icon: "fas fa-faucet" },
    { id: "electrical", name: "Electrical", description: "Wiring, outlets, switches", icon: "fas fa-plug" }
];

// Initialize the app
function init() {
    loadProducts();
    renderCategories();
    setupEventListeners();
    loadCartFromDB(); // Get cart from database instead of localStorage
}

// Render categories with icons
function renderCategories() {
    if (!categoryContainer) return; // Guard clause if element doesn't exist
    
    categoryContainer.innerHTML = '';
    categories.forEach(category => {
        const categoryCard = document.createElement('div');
        categoryCard.className = 'category';
        categoryCard.setAttribute('data-category', category.id);
        categoryCard.innerHTML = `
            <i class="${category.icon}"></i>
            <h3>${category.name}</h3>
            <p>${category.description}</p>
        `;
        categoryCard.addEventListener('click', () => filterByCategory(category.id));
        categoryContainer.appendChild(categoryCard);
    });
}

// Filter products by category
function filterByCategory(categoryId) {
    currentCategory = categoryId;
    
    // Update active state of category cards
    document.querySelectorAll('.category').forEach(card => {
        if (card.getAttribute('data-category') === categoryId) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });

    let filteredProducts;
    if (categoryId === 'all') {
        filteredProducts = products;
    } else {
        filteredProducts = products.filter(product => product.category === categoryId);
    }

    // Apply search filter if there's a search term
    if (searchTerm) {
        filteredProducts = filteredProducts.filter(product =>
            product.name.toLowerCase().includes(searchTerm) ||
            (product.description && product.description.toLowerCase().includes(searchTerm))
        );
    }

    renderProducts(filteredProducts);
}

// Render products to the page
function renderProducts(productsToRender) {
    if (!productContainer) return; // Guard clause if element doesn't exist
    
    productContainer.innerHTML = '';

    if (productsToRender.length === 0) {
        productContainer.innerHTML = '<p class="no-products">No products found matching your criteria</p>';
        return;
    }

    productsToRender.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product';
        productCard.setAttribute('data-id', product.id);
        
        // Create a description if it doesn't exist
        const description = product.description || `${product.name} - ${product.category}`;
        
        productCard.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>Category: ${product.category}</p>
            <p>Price: $${parseFloat(product.price).toFixed(2)}</p>
            <button class="add-to-cart" data-id="${product.id}">ADD TO CART</button>
        `;
        productContainer.appendChild(productCard);
    });
}

// Set up event listeners
function setupEventListeners() {
    // Add to cart buttons using event delegation
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-to-cart')) {
            const productId = parseInt(e.target.getAttribute('data-id'));
            addToCart(productId);
        }
    });

    // Cart icon click
    if (cartIcon) {
        cartIcon.addEventListener('click', openCartModal);
    }

    // Close modal
    if (closeModal) {
        closeModal.addEventListener('click', closeCartModal);
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === cartModal) {
            closeCartModal();
        }
    });

    // Hamburger menu for mobile
    if (hamburger) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }

    // Search functionality
    if (searchBtn) {
        searchBtn.addEventListener('click', searchProducts);
    }
    
    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    }
}

function searchProducts(event) {
    if (event) {
        event.preventDefault();
    }
    
    searchTerm = searchInput.value.trim().toLowerCase();

    if (searchTerm) {
        fetch('search_product.php?term=' + encodeURIComponent(searchTerm))
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                renderProducts(data); // Pass the fetched data directly
            } else {
                console.error('Expected an array but received:', data);
                productContainer.innerHTML = '<p class="no-products">No products found.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productContainer.innerHTML = '<p class="no-products">Error searching products.</p>';
        });
    } else {
        renderProducts(products); // Show all products if no search term
    }
}

function renderProducts(productsToRender) {
    if (!productContainer) return; // Guard clause if element doesn't exist
    
    productContainer.innerHTML = '';

    if (productsToRender.length === 0) {
        productContainer.innerHTML = '<p class="no-products">No products found matching your criteria</p>';
        return;
    }

    productsToRender.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product';
        productCard.setAttribute('data-id', product.id);
        
        // Log the product price for debugging
        console.log('Product Price:', product.price); // Debugging line
        
        productCard.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>Category: ${product.category}</p>
            <p>Price: RS ${parseFloat(product.price).toFixed(2)}</p>
            <button class="add-to-cart" data-id="${product.id}">ADD TO CART</button>
        `;
        productContainer.appendChild(productCard);
    });
}

function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response from server:', data); // Debugging line
        if (data.success) {
            // Update local cart display
            loadCartFromDB();
            
            // Find product name for notification
            const product = products.find(p => p.id == productId);
            const productName = product ? product.name : 'Product';

            console.log(`Adding ${productName} to cart`); // Debugging line
            showPopupNotification(`${productName} added to cart`, 'success');
            
            // Animation for cart icon
            if (cartIcon) {
                cartIcon.classList.add('animate');
                setTimeout(() => {
                    cartIcon.classList.remove('animate');
                }, 500);
            }
        } else {
            console.error('Failed to add to cart:', data.message);
            showPopupNotification('Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showPopupNotification('Error adding product to cart', 'error');
    });
}

// Function to show popup notifications
function showPopupNotification(message, type = 'success') {
    console.log('Showing notification:', message);
    const notification = document.createElement('div');
    notification.className = 'popup-notification';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <div class="notification-content">${message}</div>
        <button class="close-notification">&times;</button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    const timeout = setTimeout(() => {
        closeNotification(notification);
    }, 3000);
    const closeButton = notification.querySelector('.close-notification');
    closeButton.addEventListener('click', () => {
        clearTimeout(timeout);
        closeNotification(notification);
    });
}

// Function to close and remove notification
function closeNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        notification.remove();
    }, 300); // Match with CSS transition duration
}

// Update cart UI
function updateCart() {
    if (!cartCount) return;
    
    const totalItems = cart.reduce((total, item) => total + parseInt(item.quantity), 0);
    cartCount.textContent = totalItems;
    renderCartItems();
}

// Render cart items in modal
function renderCartItems() {
    if (!cartItems || !cartTotal) return;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
        cartTotal.textContent = '0.00';
        return;
    }

    cartItems.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
        total += itemTotal;

        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-img">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-details">
                <h4>${item.name}</h4>
                <p>RS ${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
            </div>
            <div class="cart-item-actions">
                <button class="quantity-btn decrease-quantity" data-id="${item.product_id}" data-cart-id="${item.id}">-</button>
                <span>${item.quantity}</span>
                <button class="quantity-btn increase-quantity" data-id="${item.product_id}" data-cart-id="${item.id}">+</button>
                <button class="remove-btn" data-cart-id="${item.id}">Remove</button>
            </div>
        `;
        cartItems.appendChild(cartItem);
    });

    cartTotal.textContent = total.toFixed(2);

    // Add event listeners for quantity buttons and remove buttons
    document.querySelectorAll('.decrease-quantity').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = parseInt(e.target.getAttribute('data-id'));
            updateQuantityInDB(productId, -1);
        });
    });

    document.querySelectorAll('.increase-quantity').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = parseInt(e.target.getAttribute('data-id'));
            updateQuantityInDB(productId, 1);
        });
    });

    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const cartId = parseInt(e.target.getAttribute('data-cart-id'));
            removeFromCartDB(cartId);
        });
    });
}

// Update item quantity in database
function updateQuantityInDB(productId, change) {
    fetch('update_cart_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity_change: change
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartFromDB();
        } else {
            console.error('Failed to update quantity:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
    });
}

// Remove item from cart in database
function removeFromCartDB(cartId) {
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cart_id: cartId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartFromDB();
        } else {
            console.error('Failed to remove item:', data.message);
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
    });
}

// Load cart from database
function loadCartFromDB() {
    fetch('get_cart.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.items;
            updateCart();
        } else {
            console.error('Failed to load cart:', data.message);
        }
    })
    .catch(error => {
        console.error('Error loading cart:', error);
    });
}

// Open cart modal
function openCartModal() {
    if (!cartModal) return;
    
    // First refresh cart data from database
    loadCartFromDB();
    
    cartModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Close cart modal
function closeCartModal() {
    if (!cartModal) return;
    
    cartModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Toggle mobile menu
function toggleMobileMenu() {
    if (!navLinks || !hamburger) return;
    
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');

    const lines = document.querySelectorAll('.hamburger .line');
    if (navLinks.classList.contains('active')) {
        lines[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        lines[1].style.opacity = '0';
        lines[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
    } else {
        lines[0].style.transform = 'rotate(0) translate(0)';
        lines[1].style.opacity = '1';
        lines[2].style.transform = 'rotate(0) translate(0)';
    }
}

// Call init to start the application
document.addEventListener('DOMContentLoaded', init);
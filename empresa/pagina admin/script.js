document.addEventListener('DOMContentLoaded', function() {
    const productForm = document.getElementById('productForm');
    const productTable = document.getElementById('productTable');
    const productPreview = document.getElementById('productPreview');
    let products = [];

    productForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const productId = document.getElementById('productId').value;
        const productName = document.getElementById('productName').value;
        const productPrice = document.getElementById('productPrice').value;
        const productStock = document.getElementById('productStock').value;
        const productImage = document.getElementById('productImage').files[0];
        const productDescription = document.getElementById('productDescription').value;

        const reader = new FileReader();
        reader.onload = function(e) {
            const imageUrl = e.target.result;

            if (productId) {
                // Editar producto existente
                const product = products.find(p => p.id == productId);
                product.name = productName;
                product.price = productPrice;
                product.stock = productStock;
                product.image = imageUrl;
                product.description = productDescription;
            } else {
                // Agregar nuevo producto
                const newProduct = {
                    id: Date.now(),
                    name: productName,
                    price: productPrice,
                    stock: productStock,
                    image: imageUrl,
                    description: productDescription
                };
                products.push(newProduct);
            }

            renderProducts();
            productForm.reset();
        };
        reader.readAsDataURL(productImage);
    });

    function renderProducts() {
        productTable.innerHTML = '';
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.name}</td>
                <td>${product.price}</td>
                <td>${product.stock}</td>
                <td><img src="${product.image}" alt="${product.name}" width="50"></td>
                <td>
                    <button onclick="toggleDescription(${product.id})">Ver/Ocultar</button>
                    <div id="description-${product.id}" style="display: none;">${product.description}</div>
                </td>
                <td>
                    <button onclick="editProduct(${product.id})">Editar</button>
                    <button onclick="deleteProduct(${product.id})">Eliminar</button>
                </td>
            `;
            productTable.appendChild(row);
        });
    }

    window.editProduct = function(id) {
        const product = products.find(p => p.id == id);
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productPrice').value = product.price;
        document.getElementById('productStock').value = product.stock;
        document.getElementById('productDescription').value = product.description;
        productPreview.innerHTML = `
            <h3>${product.name}</h3>
            <p>Precio: ${product.price}</p>
            <p>Stock: ${product.stock}</p>
            <p>${product.description}</p>
            <img src="${product.image}" alt="${product.name}">
        `;
    };

    window.deleteProduct = function(id) {
        products = products.filter(p => p.id != id);
        renderProducts();
    };

    window.toggleDescription = function(id) {
        const descriptionDiv = document.getElementById(`description-${id}`);
        if (descriptionDiv.style.display === 'none') {
            descriptionDiv.style.display = 'block';
        } else {
            descriptionDiv.style.display = 'none';
        }
    };
});

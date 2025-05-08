// Fetch items when modal opens
document.getElementById('addFeeItemModal').addEventListener('show.bs.modal', function () {
    fetchItems();
});

function fetchItems() {
    fetch('../api/items/get_items.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('fee_item_name');
                // Clear existing options except first and last
                while (select.options.length > 2) {
                    select.remove(1);
                }

                // Add items before the custom option
                data.items.forEach(item => {
                    const option = new Option(
                        `${item.item_name} - ₱${parseFloat(item.item_price).toFixed(2)}`,
                        item.item_id
                    );
                    option.dataset.price = item.item_price;
                    option.dataset.name = item.item_name;
                    select.insertBefore(option, select.lastChild);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Handle item selection
document.getElementById('fee_item_name').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const customItemContainer = document.getElementById('customItemNameContainer');
    const priceInput = document.getElementById('fee_item_price');

    if (this.value === 'custom') {
        customItemContainer.style.display = 'block';
        priceInput.value = '';
    } else {
        customItemContainer.style.display = 'none';
        if (selectedOption.dataset.price) {
            priceInput.value = selectedOption.dataset.price;
        }
    }
});
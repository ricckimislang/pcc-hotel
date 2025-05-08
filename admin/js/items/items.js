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

// Function to update grand total
function updateGrandTotal() {
    const roomTotalElement = document.querySelector('.room-total');
    const additionalFeesElement = document.querySelector('.additional-fees');
    const grandTotalElement = document.querySelector('.grand-total');

    // Get room total amount (remove ₱ symbol and convert to number)
    const roomTotal = parseFloat(roomTotalElement.textContent.replace('₱', '')) || 0;
    
    // Get additional fees total
    const additionalFees = parseFloat(additionalFeesElement.textContent.replace('₱', '')) || 0;
    
    // Calculate grand total
    const grandTotal = roomTotal + additionalFees;
    
    // Update grand total display with peso symbol and 2 decimal places
    grandTotalElement.textContent = `₱${grandTotal.toFixed(2)}`;
}

// Call updateGrandTotal whenever additional fees change
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'characterData' || mutation.type === 'childList') {
            updateGrandTotal();
        }
    });
});

// Start observing the additional fees element for changes
document.addEventListener('DOMContentLoaded', function() {
    const additionalFeesElement = document.querySelector('.additional-fees');
    if (additionalFeesElement) {
        observer.observe(additionalFeesElement, { 
            characterData: true, 
            childList: true, 
            subtree: true 
        });
    }
});

// Update grand total when the payment modal opens
document.getElementById('processPaymentModal').addEventListener('show.bs.modal', function () {
    updateGrandTotal();
});
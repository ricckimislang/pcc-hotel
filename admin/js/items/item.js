let itemsTable = null;

$(document).ready(function () {
    // Initialize DataTable
    itemsTable = $('#itemsTable').DataTable({
        responsive: true,
        ajax: {
            url: '../api/items/item_table.php',
            type: 'GET',
            dataSrc: function (json) {
                return json.status === 'success' ? json.data : [];
            }
        },
        columns: [
            { data: 'item_name' },
            {
                data: 'item_price',
                render: function (data) {
                    return '₱' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'updated_at',
                render: function (data) {
                    return data ? new Date(data).toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric',
                        hour12: true
                    }) : '-';
                }
            },
            {
                data: null,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-primary edit-item" data-id="${data.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-item" data-id="${data.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']], // Sort by item name ascending by default
        pageLength: 10,
        language: {
            emptyTable: "No items found",
            zeroRecords: "No matching items found"
        }
    });

    // Load Summary Data
    function loadSummary() {
        $.ajax({
            url: '../api/items/get_summary.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#totalItems').text(data.total_items);
                $('#averagePrice').text('₱' + parseFloat(data.average_price).toFixed(2));
                $('#lastUpdate').text(data.last_update ? new Date(data.last_update).toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }) : '-');
            }
        });
    }

    // Initial load
    loadSummary();

    // Add Item Form Submit
    $('#addItemForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '../api/items/add_item.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#addItemModal').modal('hide');
                    $('#addItemForm')[0].reset();
                    itemsTable.ajax.reload();
                    loadSummary();
                    toastr.success('Item added successfully');
                } else {
                    toastr.error(data.message || 'Error adding item');
                }
            }
        });
    });

    // Edit Item
    $(document).on('click', '.edit-item', function () {
        const itemId = $(this).data('id');

        $.ajax({
            url: '../api/items/get_item.php',
            type: 'GET',
            data: { item_id: itemId },
            success: function (response) {
                const data = JSON.parse(response);
                $('#editItemId').val(data.item_id);
                $('#editItemName').val(data.item_name);
                $('#editItemPrice').val(data.item_price);
                $('#editItemDescription').val(data.item_description);
                $('#editItemModal').modal('show');
            }
        });
    });

    // Edit Item Form Submit
    $('#editItemForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '../api/items/update_item.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#editItemModal').modal('hide');
                    itemsTable.ajax.reload();
                    loadSummary();
                    toastr.success('Item updated successfully');
                } else {
                    toastr.error(data.message || 'Error updating item');
                }
            }
        });
    });

    // Delete Item
    $(document).on('click', '.delete-item', function () {
        const itemId = $(this).data('id');
        $('#deleteItemId').val(itemId);
        $('#deleteItemModal').modal('show');
    });

    // Confirm Delete
    $('#confirmDeleteItem').on('click', function () {
        const itemId = $('#deleteItemId').val();

        $.ajax({
            url: '../api/items/delete_item.php',
            type: 'POST',
            data: { item_id: itemId },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#deleteItemModal').modal('hide');
                    itemsTable.ajax.reload();
                    loadSummary();
                    toastr.success('Item deleted successfully');
                } else {
                    toastr.error(data.message || 'Error deleting item');
                }
            }
        });
    });
}); 
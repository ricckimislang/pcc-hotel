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
                        <button class="btn btn-sm btn-primary edit-item" data-id="${data.item_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-item" data-id="${data.item_id}">
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
                try {
                    if (response.status === 'success') {
                        $('#editItemId').val(response.data.item_id);
                        $('#editItemName').val(response.data.item_name);
                        $('#editItemPrice').val(response.data.item_price);
                        $('#editItemDescription').val(response.data.item_description);
                        $('#editItemModal').modal('show');
                    } else {
                        alert(response.message || 'Error fetching item details');
                    }
                } catch (e) {
                    alert('Error parsing server response');
                }
            },
            error: function() {
                alert('Error fetching item details');
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
                try {
                    const data = response;
                    if (data.status === 'success') {
                        $('#editItemModal').modal('hide');
                        itemsTable.ajax.reload();
                        loadSummary();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Item updated successfully'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error updating item'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error parsing server response'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error', 
                    title: 'Error',
                    text: 'Error updating item'
                });
            }
        });
    });

    // Delete Item
    $(document).on('click', '.delete-item', function () {
        const itemId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../api/items/delete_item.php',
                    type: 'POST',
                    data: { item_id: itemId },
                    dataType: 'json', // Specify that we expect JSON response
                    success: function (data) {
                        if (data.status === 'success') {
                            itemsTable.ajax.reload();
                            loadSummary();
                            Swal.fire(
                                'Deleted!',
                                'Item has been deleted.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message || 'Error deleting item',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error deleting item',
                            'error'
                        );
                    }
                });
            }
        });
    });
}); 
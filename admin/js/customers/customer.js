function na(val) {
    return (val && val !== '' && val !== null && val !== undefined) ? val : 'N/A';
}

function renderCustomerCard(customer) {
    let img = na(customer.profile_image);
    let imgSrc = (img !== 'N/A') ? ('../uploads/profile_images/' + img) : '../assets/img/default-profile.png';

    return `
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card customer-card h-100 shadow-sm border-0">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <div class="d-flex align-items-center">
                <div class="customer-avatar-wrapper me-3">
                    <img src="${imgSrc}" alt="${na(customer.fullname)}" class="customer-avatar">
                </div>
                <div>
                    <h5 class="customer-name mb-1">${na(customer.fullname)}</h5>
                    <div class="customer-phone text-muted">
                        <i class="fas fa-phone-alt me-2"></i>${na(customer.phone)}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="customer-detail-row">
                <div class="row g-0">
                    <div class="col-6 p-3 text-center border-end">
                        <div class="detail-icon mb-2">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="detail-label">Frequent Guest</div>
                        <div class="detail-value">${na(customer.frequent_guest)}</div>
                    </div>
                    <div class="col-6 p-3 text-center">
                        <div class="detail-icon mb-2">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="detail-label">Loyal Points</div>
                        <div class="detail-value">${na(customer.loyal_points)}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="card-footer bg-transparent border-0 pb-4">
            <button class="btn btn-outline-primary btn-sm w-100">
                <i class="fas fa-user-edit me-2"></i>View Details
            </button>
        </div> -->
    </div>
</div>`;
}

// Function to load all customers
function loadAllCustomers() {
    $('#loading-spinner').show();

    $.ajax({
        url: '../api/customers/get_all_customers.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            $('#loading-spinner').hide();

            if (res.success && res.data.length > 0) {
                $('#customer-results').empty();
                $('#no-results').hide();

                res.data.forEach(function(customer) {
                    $('#customer-results').append(renderCustomerCard(customer));
                });
            } else {
                $('#customer-results').empty();
                $('#no-results').show();
            }
        },
        error: function() {
            $('#loading-spinner').hide();
            $('#customer-results').empty();
            $('#no-results').show();
        }
    });
}

$(function() {
    // Load all customers when page loads
    loadAllCustomers();

    let debounceTimer;

    $('#search-username').on('input', function() {
        clearTimeout(debounceTimer);
        const searchTerm = $(this).val().trim();

        if (searchTerm.length < 2) {
            // If search field is cleared, show all customers again
            loadAllCustomers();
            return;
        }

        $('#loading-spinner').show();

        debounceTimer = setTimeout(function() {
            $.ajax({
                url: '../api/customers/search_customer.php',
                method: 'POST',
                data: {
                    search_term: searchTerm
                },
                dataType: 'json',
                success: function(res) {
                    $('#loading-spinner').hide();

                    if (res.success && res.data.length > 0) {
                        $('#customer-results').empty();
                        $('#no-results').hide();

                        res.data.forEach(function(customer) {
                            $('#customer-results').append(renderCustomerCard(customer));
                        });
                    } else {
                        $('#customer-results').empty();
                        $('#no-results').show();
                    }
                },
                error: function() {
                    $('#loading-spinner').hide();
                    $('#customer-results').empty();
                    $('#no-results').show();
                }
            });
        }, 350); // debounce delay
    });
});
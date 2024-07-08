<!DOCTYPE html>
<html>

<head>
    <title>Order Form</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let db;
            const request = indexedDB.open('OrderDB', 1);

            request.onerror = function(event) {
                console.error('Database error: ' + event.target.errorCode);
            };

            request.onsuccess = function(event) {
                db = event.target.result;
                displayOrders();
            };

            request.onupgradeneeded = function(event) {
                db = event.target.result;
                db.createObjectStore('orders', {
                    keyPath: 'id',
                    autoIncrement: true
                });
            };

            $('#orderForm').on('submit', function(event) {
                event.preventDefault();
                const customerName = $('#customerName').val();
                const orderValue = $('#orderValue').val();

                const transaction = db.transaction(['orders'], 'readwrite');
                const store = transaction.objectStore('orders');
                const request = store.add({
                    customerName,
                    orderValue
                });

                request.onsuccess = function() {
                    displayOrders();
                };

                // AJAX request to Laravel backend
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/new-order',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        customer_name: $('#customerName').val(),
                        order_value: $('#orderValue').val()
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseJSON);
                    }
                });


            });

            function displayOrders() {
                const transaction = db.transaction(['orders'], 'readonly');
                const store = transaction.objectStore('orders');
                const request = store.getAll();

                request.onsuccess = function(event) {
                    const orders = event.target.result;
                    let tableContent = '<tr><th>ID</th><th>Customer Name</th><th>Order Value</th></tr>';
                    orders.forEach(order => {
                        tableContent += `<tr><td>${order.id}</td><td>${order.customerName}</td><td>${order.orderValue}</td></tr>`;
                    });
                    $('#orderTable').html(tableContent);
                };
            }
        });
    </script>
</head>

<body>
    <h1>New Order</h1>
    <form id="orderForm">
        <label for="customerName">Customer Name:</label><br>
        <input type="text" id="customerName" name="customerName"><br>
        <label for="orderValue">Order Value:</label><br>
        <input type="text" id="orderValue" name="orderValue"><br><br>
        <input type="submit" value="Submit">
    </form>
    <h2>Order List</h2>
    <table id="orderTable" border="1">
    </table>
</body>

</html>
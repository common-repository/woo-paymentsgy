const mockyeah = require('mockyeah');

mockyeah.get('', {
    status: 400
});

mockyeah.post('payment_requests', {
    status: 422
});

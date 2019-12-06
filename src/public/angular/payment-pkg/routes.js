app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //Payment
    when('/payment-pkg/payments/:type_id', {
        template: '<payments></payments>',
        title: 'Payments',
    }).
    when('/payment-pkg/payment/view/:id', {
        template: '<payment-view></payment-view>',
        title: 'Payment',
    });
}]);
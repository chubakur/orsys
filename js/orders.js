var orsysApp = angular.module('orsysApp', []);

orsysApp.controller('OrdersController', function ($scope, $interval, $log) {
    $scope.loaded = true;
    function as(){
        $log.log(Date.now())
    };
    $interval(as, 5000);
});
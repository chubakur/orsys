var orsysApp = angular.module('orsysApp', []);

orsysApp.controller('OrdersController', function ($scope, $interval, $log) {
    $log.info("OrdersController init");
    $scope.loaded = true;
    function as(){
        $log.log(Date.now())
    };
    $interval(as, 5000);
});

orsysApp.controller('RegisterController', function ($scope, $log) {
    $log.info("RegisterController init");
    $scope.form = {
        email: undefined,
        password: undefined,
        password2: undefined,
        role: 'performer',
    }
    $scope.register = function () {
        $log.log($scope.form);
    }
});

orsysApp.controller('LoginController', function ($scope, $log){
    $log.info("LoginController init");
   $scope.login = function (){
       $log.log("Login");
   }
});
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
    };
    $scope.valid = true;
    $scope.register = function () {
        if(!$scope.valid) return;
        $log.log($scope.form);
    };
});

orsysApp.controller('LoginController', function ($scope, $log){
    $log.info("LoginController init");
   $scope.login = function (){
       $log.log("Login");
   };
});

var compareTo = function() {
    return {
        require: "ngModel",
        scope: {
            otherModelValue: "=compareTo"
        },
        link: function($scope, $element, $attributes, $ngModel) {
            $ngModel.$validators.compareTo = function(modelValue) {
                return modelValue == scope.otherModelValue;
            };

            $scope.$watch("otherModelValue", function() {
                $ngModel.$validate();
            });
        }
    };
};

orsysApp.directive("compareTo", compareTo);
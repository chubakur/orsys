var orsysApp = angular.module('orsysApp', []);

function urlencode(data){
    var args = []
    for(var key in data){
        args.push(key+"="+data[key]);
    }
    return args.join("&")
}

orsysApp.controller('OrdersController', function ($scope, $interval, $log) {
    $log.info("OrdersController init");
    $scope.loaded = true;
    function as(){
        $log.log(Date.now())
    };
    $interval(as, 5000);
});

orsysApp.controller('RegisterController', function ($scope, $http, $log) {
    $log.info("RegisterController init");
    $scope.form = {
        email: undefined,
        password: undefined,
        password2: undefined,
        role: 'performer'
    };
    $scope.register = function () {
        if($scope.registerForm.$invalid) return;
        var handler = $http.post("/register", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        handler.success(function(data) {
            $log.log(data);
        });
        handler.error(function(data){
            $log.error(data);
        });
        return true;
    }
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
                return modelValue == $scope.otherModelValue;
            };

            $scope.$watch("otherModelValue", function() {
                $ngModel.$validate();
            });
        }
    };
};

orsysApp.directive("compareTo", compareTo);
var orsysApp = angular.module('orsysApp', ['ui.bootstrap']);

function urlencode(data){
    var args = []
    for(var key in data){
        args.push(key+"="+data[key]);
    }
    return args.join("&")
}

orsysApp.factory('auth', function() {
    var auth = {
        email: undefined,
        role: undefined
    };
    return auth;
});

orsysApp.controller('OrdersController', function (auth, $scope, $interval, $log, $http) {
    $scope.auth = auth;
    $log.info("OrdersController init");
    $scope.logout = function() {
        var response = $http.get("/logout");
        response.success(function (data){
            auth.email = undefined;
            auth.role = undefined;
        });
        response.error(function (data){
            $log.error(data);
        });
    };
    function timer(){
        $log.log(Date.now());
        $log.log(auth);
    };
    $interval(timer, 5000);
});

orsysApp.controller('RegisterController', function (auth, $scope, $http, $log) {
    $log.info("RegisterController init");
    $scope.form = {
        email: undefined,
        password: undefined,
        password2: undefined,
        role: 'performer'
    };
    $scope.register_status = undefined;
    $scope.register = function () {
        if($scope.registerForm.$invalid || $scope.register_status == "start") return;
        $scope.register_status = "start";
        var handler = $http.post("/register", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        handler.success(function(data) {
            $log.log(data);
            $scope.register_status = data.status;
            if(data.status == 'ok'){
                auth.email = data.email;
                auth.role = data.role;
            }
        });
        handler.error(function(data){
            $log.error(data);
            $scope.register_status = "invalid";
        });
        return true;
    }
});

orsysApp.controller('LoginController', function (auth, $scope, $http, $log){
    $log.info("LoginController init");
    var quering = true;
    $scope.form = {
        email: undefined,
        password: undefined
    };
    $http.get("/auth").success(function (data){
        if(data.status != 'ok'){
            $log.warn(data);
            return;
        }
        auth.email = data.email;
        auth.role = data.role;
    }).error(function (data){
        $log.error(data);
    }).finally(function (){
       quering = false;
    });
    $scope.login = function (email, password){
        $log.log("Login");
        quering = true;
        var response = $http.post("/auth", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        response.success(function (data){
            if(data.status != 'ok'){
                $log.warn(data);
                return;
            }
            auth.email = data.email;
            auth.role = data.role;
        });
        response.error(function (data){
            $log.error(data);
        });
        response.finally(function (){
            quering = false;
        });
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
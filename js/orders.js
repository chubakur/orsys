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

orsysApp.controller('OrdersController', function (auth, $scope, $modal, $interval, $log, $http) {
    $scope.auth = auth;
    $log.info("OrdersController init");
    $scope.showLoginDialog = function (size){
        var modalInstance = $modal.open({
            animation: true,
            templateUrl: 'loginModal.html',
            controller: 'LoginDialogController',
            size: size
        });
    };
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

orsysApp.controller('RegisterController', function (auth, $scope, $modalInstance, $http, $log) {
    $log.info("RegisterController init");
    $scope.closeModal = function (){
        $modalInstance.close();
    };
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
                $modalInstance.close();
            }
        });
        handler.error(function(data){
            $log.error(data);
            $scope.register_status = "invalid";
        });
        return true;
    }
});

orsysApp.controller('LoginDialogController', function (auth, $scope, $modal, $modalInstance, $http, $log){
    $scope.closeModal = function (){
        $modalInstance.close();
    };
    $scope.openRegisterDialog = function(size){
        $modalInstance.close();
        var modalInstance = $modal.open({
            animation: true,
            templateUrl: 'registerModal.html',
            controller: 'RegisterController',
            size: size
        });
    };
    $scope.quering = false;
    $scope.form = {
        email: undefined,
        password: undefined
    };
    $scope.login = function (email, password){
        if($scope.quering) return;
        $scope.quering = true;
        var response = $http.post("/auth", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        response.success(function (data){
            if(data.status != 'ok'){
                $log.warn(data);
                return;
            }
            auth.email = data.email;
            auth.role = data.role;
            $modalInstance.close();
        });
        response.error(function (data){
            $log.error(data);
        });
        response.finally(function (){
            $scope.quering = false;
        });
    };
});

orsysApp.controller('LoginController', function (auth, $scope, $http, $log){
    $log.info("LoginController init");
    $scope.quering = true;
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
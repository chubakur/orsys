var orsysApp = angular.module('orsysApp', ['ui.bootstrap', 'infinite-scroll']);

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
        role: undefined,
        bill: undefined
    };
    return auth;
});

orsysApp.controller('OrdersController', function (auth, $scope, $sce, $modal, $interval, $log, $http) {
    $scope.auth = auth;
    $log.info("OrdersController init");
    $scope.orders = [];
    $scope.is_loading = false;
    $scope.last_loaded = false;
    function loadOrders(){
        $log.log("loadOrders");
        $scope.is_loading = true;
        $http.get("/feed").success(function (data){
            if(data.status == 'ok'){
                updateOrders(data.ts);
                data.results.forEach(function (result){
                    result.trusted = $sce.trustAsHtml(result.description);
                    return result;
                });
                $scope.orders = data.results;
            }
        }).error(function (data){
            $log.error(data);
        }).finally(function (){
            $scope.is_loading = false;
        });
    }
    $scope.form = {
        description: undefined,
        cost: undefined
    };
    $scope.sending = false;
    $scope.makeOrder = function(descr, cost){
        if($scope.sending) return;
        $scope.sending = true;
        var promise = $http.post("/feed_new", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}});
        promise.success(function (data){
            if(data.status == 'ok') {
                $scope.form.description = undefined;
                $scope.form.cost = undefined;
            }
        });
        promise.error(function (data){
            $log.error(data);
        });
        promise.finally(function (){
            $scope.sending = false;
        });
    };
    $scope.showLoginDialog = function (size){
        var modalInstance = $modal.open({
            animation: true,
            templateUrl: 'loginModal.html',
            controller: 'LoginDialogController',
            size: size
        });
    };
    $scope.autoStartLoginDialogOnce = function (){
        $scope.showLoginDialog();
        $scope.$$listeners.noauth = [];
    }
    $scope.completeOrder = function(selected_order){
        var promise = $http.post("/feed_complete", urlencode({order_id: selected_order.id}), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        promise.success(function (data){
            if(data.status == 'ok'){
                $scope.orders = $scope.orders.filter(function (order){
                   return order != selected_order;
                });
                auth.bill += parseInt(selected_order.cost);
            }
        });
        promise.error(function (data){
            $log.error(data);
        });
        promise.finally(function (){

        });
    };
    $scope.loadMoreOrders = function (){
        if(auth.email == undefined || $scope.is_loading || $scope.last_loaded) return;
        var min_element = $scope.orders[$scope.orders.length - 1];
        if(min_element) {
            var minid = parseInt(min_element.id);
            $scope.is_loading = true;
            $log.log("load From:" + minid);
            $http.get("/feed", {params: {minid: minid}}).success(function (data){
                $log.log(data);
                if(data.status == 'ok'){
                    if(data.results.length <= 0 ) $scope.last_loaded = true;
                    data.results.forEach(function (result){
                       result.trusted = $sce.trustAsHtml(result.description);
                    });
                    $scope.orders = $scope.orders.concat(data.results);
                }else{
                    $log.warn(data);
                }
            }).error(function (data){
                $log.error(data);
            }).finally(function (){
                $scope.is_loading = false;
            });
        }

    };
    $scope.logout = function() {
        var response = $http.get("/logout");
        response.success(function (data){
            auth.email = undefined;
            auth.role = undefined;
            $scope.orders = [];
        });
        response.error(function (data){
            $log.error(data);
        });
    };
    $scope.$on('auth', loadOrders);
    $scope.$on('noauth', $scope.autoStartLoginDialogOnce);
    function updateOrders(ts){
        if(auth.email == undefined) return;
        $log.log("UPDATE ORDERS "+ts);
        $http.post("/feed", urlencode({ts: ts}), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}).success(function (data){
            $log.log(data);
            data.events.forEach(function (event){
                if(event.event.type == 'new'){
                    event.event.trusted = $sce.trustAsHtml(event.event.description);
                    $scope.orders.unshift(event.event);
                }else if(event.event.type == 'done'){
                    $scope.orders = $scope.orders.filter(function (order){
                        return order.id != parseInt(event.event.order_id);
                    });
                }else{
                    $log.error("EVENT.TYPE");
                }
            });
            $log.info($scope.orders);
            updateOrders(data.ts);
        }).error(function (data){
            $log.error(data);
        })
    };
});

orsysApp.controller('RegisterController', function (auth, $scope, loginDialogScope, $modalInstance, $http, $log) {
    $log.info("RegisterController init");
    $scope.closeModal = function (){
        $modalInstance.close();
    };
    $scope.form = {
        email: loginDialogScope.email,
        password: loginDialogScope.password,
        password2: undefined,
        role: 'performer'
    };
    $scope.register_status = undefined;
    $scope.error_message = undefined;
    $scope.register = function () {
        if($scope.registerForm.$invalid || $scope.register_status == "start") return;
        $scope.error_message = undefined;
        $scope.register_status = "start";
        var handler = $http.post("/register", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        handler.success(function(data) {
            $log.log(data);
            $scope.register_status = data.status;
            if(data.status == 'ok'){
                auth.email = data.email;
                auth.role = data.role;
                $modalInstance.close();
                $scope.$root.$broadcast('auth');
            }else{
                $scope.error_message = data.msg;
            }
        });
        handler.error(function(data){
            $log.error(data);
            $scope.register_status = "invalid";
        });
        return true;
    };
});

orsysApp.controller('LoginDialogController', function (auth, $scope, $modal, $modalInstance, $http, $log){
    $scope.quering = false;
    $scope.error_message = undefined;
    $scope.form = {
        email: undefined,
        password: undefined
    };
    $scope.closeModal = function (){
        $modalInstance.close();
    };
    $scope.openRegisterDialog = function(size){
        $modalInstance.close();
        var modalInstance = $modal.open({
            animation: true,
            templateUrl: 'registerModal.html',
            controller: 'RegisterController',
            size: size,
            resolve: {
                loginDialogScope: function (){
                    return $scope.form;
                }
            }
        });
    };
    $scope.login = function (email, password){
        if($scope.quering) return;
        $scope.error_message = undefined;
        $scope.quering = true;
        var response = $http.post("/auth", urlencode($scope.form), {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
        response.success(function (data){
            if(data.status != 'ok'){
                $scope.error_message = data.msg;
                return;
            }
            auth.email = data.email;
            auth.role = data.role;
            auth.bill = parseInt(data.bill);
            $modalInstance.close();
            $scope.$root.$broadcast('auth');
        });
        response.error(function (data){
            $log.error(data);
        });
        response.finally(function (){
            $scope.quering = false;
        });
    };
});

orsysApp.controller('LoginController', function (auth, $scope, $interval, $http, $log){
    $log.info("LoginController init");
    $scope.quering = false;
    $scope.updateAccountInfo = function (){
        if($scope.quering) return;
        $log.log("updateAccountInfo()")
        $scope.quering = true;
        $http.get("/auth").success(function (data){
            if(data.status != 'ok'){
                $log.log("NOT LOGINED");
                $scope.$root.$broadcast('noauth');
                return;
            }
            var logined = auth.email == undefined;
            auth.email = data.email;
            auth.role = data.role;
            auth.bill = parseInt(data.bill);
            if(logined){
                $scope.$root.$broadcast('auth');
            }
        }).error(function (data){
            $log.error(data);
        }).finally(function (){
            $scope.quering = false;
        });
    }
    $interval($scope.updateAccountInfo, 5000);
    $scope.updateAccountInfo();
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

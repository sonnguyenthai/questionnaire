'use strict';

angular.module('modal-form', ['ui.bootstrap'])
.factory('formService', ['$modal', '$log', function ($modal, $log) {

  var ModalInstanceCtrl = function($scope, $http, $window, $timeout, $modalInstance, config) {

    $scope.sync = false;
    $scope.error = '';
    $scope.formInvalid = false;
    $scope.data = config.field?config.data[config.field]:config.data;
    
    $scope.ok = function (f) {

      // form input invalid
      if (f.$invalid) {
        $scope.formInvalid = true;
        return;
      }

      if (config.path) {
        $scope.sync = true;
        $scope.error = '';
        $scope.succ = false;
        $http({
          method: config.method || 'POST',
          url: config.path,
          data: $scope.data
        }).success(function(data, status, headers, cfg) {
          if (config.redirect) {
            $window.location.hash = "";
            $window.location.pathname = config.redirect;
          } else {
            if (config.callback) {
              config.callback(config.data, data);
            }

            if (config.closeOnSuccess) {
              return $modalInstance.close();
            }
            $scope.succ = true;
            $scope.sync = false;
            $timeout(function() { $scope.succ = false;}, 10000);
          }
        }).error(function(data, status) {
          $scope.sync = false;
          $scope.error = data.message || 'server response:' + status;
          $timeout(function() { $scope.error = '';}, 10000);
        });
      } else {
        if (config.callback) {
          config.callback(config.data, null);
        }

        if (config.closeOnSuccess) {
          $modalInstance.close();
        }
      }
    };

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };

  };

  var openModal = function (params) {
    // set default params
    params.data = params.data || {};
    if (!params.closeOnSuccess || params.closeOnSuccess === 'true') {
      params.closeOnSuccess = true;
    } else {
      params.closeOnSuccess = false;
    }
    params.method = params.method || 'POST';

    var modalInstance = $modal.open({
      templateUrl: params.templateUrl,
      controller: ModalInstanceCtrl,
      windowClass: params.dialogClass,
      resolve: {
        config: function() {
          return params;
        }
      }
    });

    modalInstance.result.then(function (data) {
      $log.info('modal closed.');
    }, function () {
      $log.info('modal dismissed.');
    });
  };

  return function(params) {
    return openModal.bind(null, params);
  };
}]).directive('modalForm', function() {
  return {
    restrict: 'EA',
    scope: {
      data: "=?", // data model bind to the modal dialog template
      field: "@", // data field that send to server
      templateUrl: "@",  // modal dialog template url, required
      method: "@",  // ajax request method, POST, PUT, etc, defaults to POST
      path: "@",   // ajax request path
      dialogClass: "@",   // same as in ui bootstrap modal
      redirect: "@",    // redirect path on success
      closeOnSuccess: "@",    // close modal on success, defaults to true
      callback: "&"   // callback function
    },
    controller: ['$scope', 'formService', function($scope, formService) {
      $scope.open = formService($scope);
    }],
    link: function(scope, element, attrs) {
      element.click(function() {scope.open();});
    }
  };
});

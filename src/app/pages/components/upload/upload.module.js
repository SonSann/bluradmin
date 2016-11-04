/**
 * @author a.demeshko
 * created on 12.21.2015
 */
(function () {
  'use strict';

  angular.module('BlurAdmin.pages.components.upload', [])
    .config(routeConfig);

  /** @ngInject */
  function routeConfig($stateProvider) {
    $stateProvider
        .state('components.upload', {
          url: '/upload',
          templateUrl: 'app/pages/components/upload/upload.html',
          title: 'Upload',
          sidebarMeta: {
            order: 300,
          },
        });
  }

})();

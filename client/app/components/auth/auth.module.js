'use strict';

angular.module('BlurAdmin.auth', [
  'BlurAdmin.util',
  'ngCookies',
  'ngRoute'
])
  .config(function($httpProvider) {
    $httpProvider.interceptors.push('authInterceptor');
  });

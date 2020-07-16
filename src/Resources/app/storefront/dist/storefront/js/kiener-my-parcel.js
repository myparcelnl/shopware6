(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["kiener-my-parcel"],{

/***/ "GR9V":
/*!*************************************************************************************************************************************************!*\
  !*** /Users/kiener/Webroot/sw6_repo/custom/plugins/KienerMyParcel/src/Resources/app/storefront/src/myparcel/plugins/shipping-options.plugin.js ***!
  \*************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return MyParcelShippingOptions; });\n/* harmony import */ var src_plugin_system_plugin_class__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! src/plugin-system/plugin.class */ \"FGIj\");\n/* harmony import */ var src_helper_storage_cookie_storage_helper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! src/helper/storage/cookie-storage.helper */ \"prSB\");\nfunction _typeof(obj) { if (typeof Symbol === \"function\" && typeof Symbol.iterator === \"symbol\") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === \"function\" && obj.constructor === Symbol && obj !== Symbol.prototype ? \"symbol\" : typeof obj; }; } return _typeof(obj); }\n\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nfunction _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }\n\nfunction _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }\n\nfunction _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === \"object\" || typeof call === \"function\")) { return call; } return _assertThisInitialized(self); }\n\nfunction _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError(\"this hasn't been initialised - super() hasn't been called\"); } return self; }\n\nfunction _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }\n\nfunction _inherits(subClass, superClass) { if (typeof superClass !== \"function\" && superClass !== null) { throw new TypeError(\"Super expression must either be null or a function\"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }\n\nfunction _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }\n\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n\n\n\nvar MyParcelShippingOptions =\n/*#__PURE__*/\nfunction (_Plugin) {\n  _inherits(MyParcelShippingOptions, _Plugin);\n\n  function MyParcelShippingOptions() {\n    _classCallCheck(this, MyParcelShippingOptions);\n\n    return _possibleConstructorReturn(this, _getPrototypeOf(MyParcelShippingOptions).apply(this, arguments));\n  }\n\n  _createClass(MyParcelShippingOptions, [{\n    key: \"init\",\n    // get shipping form\n    value: function init() {\n      var me = this;\n      var shippingForms = document.querySelectorAll('.myparcel_shipping_form'); // CookieStorage.setItem(me.options.cookieName, 'test');\n      // CookieStorage.setItem('allowCookie', '');\n\n      var cookiePermission = src_helper_storage_cookie_storage_helper__WEBPACK_IMPORTED_MODULE_1__[\"default\"].getItem(me.options.cookieName);\n      console.log('Cookie: ' + cookiePermission); // CookieStorage.setItem(cookieName, '1', cookieExpiration);\n\n      if (shippingForms) {\n        shippingForms.forEach(function (shippingForm) {\n          var shippingMethodId = shippingForm.getAttribute('data-shipping-method-id'); // const deliveryOptionInputs = shippingForm.querySelectorAll('input[name=\"myparcel_delivery_type\"]');\n          // const requiresSignatureInput = shippingForm.querySelector('input[name=\"myparcel_requires_signature\"]');\n          // const onlyRecipientInput = shippingForm.querySelector('input[name=\"myparcel_only_recipient\"]');\n\n          console.log(shippingMethodId + ' - v3'); // deliveryOptionInputs.forEach(function (deliveryOptionInput) {\n          //     deliveryOptionInput.addEventListener('change', function() {\n          //         const targetName = deliveryOptionInput.getAttribute('data-target');\n          //         document.querySelector(targetName).value(deliveryOptionInput.value);\n          //         console.log('del option val ' + deliveryOptionInput.value + ' target ' + targetName);\n          //     });\n          // });\n          //\n          // requiresSignatureInput.addEventListener('change', function() {\n          //     const targetName = requiresSignatureInput.getAttribute('data-target');\n          //     console.log(targetName);\n          // });\n          //\n          // onlyRecipientInput.addEventListener('change', function() {\n          //     const targetName = onlyRecipientInput.getAttribute('data-target');\n          //     console.log(targetName);\n          // });\n        }); // document.querySelectorAll('.myparcel_shipping_form').addEventListener('change', function() {\n        //     console.log('Changed!');\n        // });\n        // // document.getElementById(\"select\").onchange = function() { console.log(\"Changed!\"); }\n        //\n        // // document.addEventListener('click', function (event) {\n        // //\n        // //     // If the clicked element doesn't have the right selector, bail\n        // //     if (!event.target.matches('.click-me')) return;\n        // //\n        // //     // Don't follow the link\n        // //     event.preventDefault();\n        // //\n        // //     // Log the clicked element in the console\n        // //     console.log(event.target);\n        // //\n        // // }, false);\n      }\n    }\n  }]);\n\n  return MyParcelShippingOptions;\n}(src_plugin_system_plugin_class__WEBPACK_IMPORTED_MODULE_0__[\"default\"]);\n\n_defineProperty(MyParcelShippingOptions, \"options\", {\n  /**\n   * cookie set to determine if cookies were accepted or denied\n   */\n  cookieName: 'myparcel-cookie-key' // cookieName: 'cookie.groupRequiredDescription'\n\n  /**\n   * container selector\n   */\n  // targetContainer: '.js-cookie-permission-button'\n\n});\n\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiR1I5Vi5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8vVXNlcnMva2llbmVyL1dlYnJvb3Qvc3c2X3JlcG8vY3VzdG9tL3BsdWdpbnMvS2llbmVyTXlQYXJjZWwvc3JjL1Jlc291cmNlcy9hcHAvc3RvcmVmcm9udC9zcmMvbXlwYXJjZWwvcGx1Z2lucy9zaGlwcGluZy1vcHRpb25zLnBsdWdpbi5qcz9jMzcxIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCBQbHVnaW4gZnJvbSAnc3JjL3BsdWdpbi1zeXN0ZW0vcGx1Z2luLmNsYXNzJztcbmltcG9ydCBDb29raWVTdG9yYWdlIGZyb20gJ3NyYy9oZWxwZXIvc3RvcmFnZS9jb29raWUtc3RvcmFnZS5oZWxwZXInO1xuXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBNeVBhcmNlbFNoaXBwaW5nT3B0aW9ucyBleHRlbmRzIFBsdWdpbiB7XG5cbiAgICBzdGF0aWMgb3B0aW9ucyA9IHtcblxuICAgICAgICAvKipcbiAgICAgICAgICogY29va2llIHNldCB0byBkZXRlcm1pbmUgaWYgY29va2llcyB3ZXJlIGFjY2VwdGVkIG9yIGRlbmllZFxuICAgICAgICAgKi9cbiAgICAgICAgY29va2llTmFtZTogJ215cGFyY2VsLWNvb2tpZS1rZXknXG4gICAgICAgIC8vIGNvb2tpZU5hbWU6ICdjb29raWUuZ3JvdXBSZXF1aXJlZERlc2NyaXB0aW9uJ1xuXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBjb250YWluZXIgc2VsZWN0b3JcbiAgICAgICAgICovXG4gICAgICAgIC8vIHRhcmdldENvbnRhaW5lcjogJy5qcy1jb29raWUtcGVybWlzc2lvbi1idXR0b24nXG4gICAgfTtcblxuICAgIC8vIGdldCBzaGlwcGluZyBmb3JtXG4gICAgaW5pdCgpIHtcbiAgICAgICAgY29uc3QgbWUgPSB0aGlzO1xuXG4gICAgICAgIGNvbnN0IHNoaXBwaW5nRm9ybXMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcubXlwYXJjZWxfc2hpcHBpbmdfZm9ybScpO1xuICAgICAgICAvLyBDb29raWVTdG9yYWdlLnNldEl0ZW0obWUub3B0aW9ucy5jb29raWVOYW1lLCAndGVzdCcpO1xuICAgICAgICAvLyBDb29raWVTdG9yYWdlLnNldEl0ZW0oJ2FsbG93Q29va2llJywgJycpO1xuICAgICAgICBjb25zdCBjb29raWVQZXJtaXNzaW9uID0gQ29va2llU3RvcmFnZS5nZXRJdGVtKG1lLm9wdGlvbnMuY29va2llTmFtZSk7XG4gICAgICAgIGNvbnNvbGUubG9nKCdDb29raWU6ICcgKyBjb29raWVQZXJtaXNzaW9uKTtcbiAgICAgICAgLy8gQ29va2llU3RvcmFnZS5zZXRJdGVtKGNvb2tpZU5hbWUsICcxJywgY29va2llRXhwaXJhdGlvbik7XG5cbiAgICAgICAgaWYgKHNoaXBwaW5nRm9ybXMpIHtcbiAgICAgICAgICAgIHNoaXBwaW5nRm9ybXMuZm9yRWFjaChmdW5jdGlvbiAoc2hpcHBpbmdGb3JtKSB7XG4gICAgICAgICAgICAgICAgY29uc3Qgc2hpcHBpbmdNZXRob2RJZCA9IHNoaXBwaW5nRm9ybS5nZXRBdHRyaWJ1dGUoJ2RhdGEtc2hpcHBpbmctbWV0aG9kLWlkJyk7XG4gICAgICAgICAgICAgICAgLy8gY29uc3QgZGVsaXZlcnlPcHRpb25JbnB1dHMgPSBzaGlwcGluZ0Zvcm0ucXVlcnlTZWxlY3RvckFsbCgnaW5wdXRbbmFtZT1cIm15cGFyY2VsX2RlbGl2ZXJ5X3R5cGVcIl0nKTtcbiAgICAgICAgICAgICAgICAvLyBjb25zdCByZXF1aXJlc1NpZ25hdHVyZUlucHV0ID0gc2hpcHBpbmdGb3JtLnF1ZXJ5U2VsZWN0b3IoJ2lucHV0W25hbWU9XCJteXBhcmNlbF9yZXF1aXJlc19zaWduYXR1cmVcIl0nKTtcbiAgICAgICAgICAgICAgICAvLyBjb25zdCBvbmx5UmVjaXBpZW50SW5wdXQgPSBzaGlwcGluZ0Zvcm0ucXVlcnlTZWxlY3RvcignaW5wdXRbbmFtZT1cIm15cGFyY2VsX29ubHlfcmVjaXBpZW50XCJdJyk7XG5cbiAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhzaGlwcGluZ01ldGhvZElkICsgJyAtIHYzJyk7XG4gICAgICAgICAgICAgICAgLy8gZGVsaXZlcnlPcHRpb25JbnB1dHMuZm9yRWFjaChmdW5jdGlvbiAoZGVsaXZlcnlPcHRpb25JbnB1dCkge1xuICAgICAgICAgICAgICAgIC8vICAgICBkZWxpdmVyeU9wdGlvbklucHV0LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIC8vICAgICAgICAgY29uc3QgdGFyZ2V0TmFtZSA9IGRlbGl2ZXJ5T3B0aW9uSW5wdXQuZ2V0QXR0cmlidXRlKCdkYXRhLXRhcmdldCcpO1xuICAgICAgICAgICAgICAgIC8vICAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3Rvcih0YXJnZXROYW1lKS52YWx1ZShkZWxpdmVyeU9wdGlvbklucHV0LnZhbHVlKTtcbiAgICAgICAgICAgICAgICAvLyAgICAgICAgIGNvbnNvbGUubG9nKCdkZWwgb3B0aW9uIHZhbCAnICsgZGVsaXZlcnlPcHRpb25JbnB1dC52YWx1ZSArICcgdGFyZ2V0ICcgKyB0YXJnZXROYW1lKTtcbiAgICAgICAgICAgICAgICAvLyAgICAgfSk7XG4gICAgICAgICAgICAgICAgLy8gfSk7XG4gICAgICAgICAgICAgICAgLy9cbiAgICAgICAgICAgICAgICAvLyByZXF1aXJlc1NpZ25hdHVyZUlucHV0LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIC8vICAgICBjb25zdCB0YXJnZXROYW1lID0gcmVxdWlyZXNTaWduYXR1cmVJbnB1dC5nZXRBdHRyaWJ1dGUoJ2RhdGEtdGFyZ2V0Jyk7XG4gICAgICAgICAgICAgICAgLy8gICAgIGNvbnNvbGUubG9nKHRhcmdldE5hbWUpO1xuICAgICAgICAgICAgICAgIC8vIH0pO1xuICAgICAgICAgICAgICAgIC8vXG4gICAgICAgICAgICAgICAgLy8gb25seVJlY2lwaWVudElucHV0LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIC8vICAgICBjb25zdCB0YXJnZXROYW1lID0gb25seVJlY2lwaWVudElucHV0LmdldEF0dHJpYnV0ZSgnZGF0YS10YXJnZXQnKTtcbiAgICAgICAgICAgICAgICAvLyAgICAgY29uc29sZS5sb2codGFyZ2V0TmFtZSk7XG4gICAgICAgICAgICAgICAgLy8gfSk7XG4gICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgLy8gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLm15cGFyY2VsX3NoaXBwaW5nX2Zvcm0nKS5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIC8vICAgICBjb25zb2xlLmxvZygnQ2hhbmdlZCEnKTtcbiAgICAgICAgICAgIC8vIH0pO1xuICAgICAgICAgICAgLy8gLy8gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCJzZWxlY3RcIikub25jaGFuZ2UgPSBmdW5jdGlvbigpIHsgY29uc29sZS5sb2coXCJDaGFuZ2VkIVwiKTsgfVxuICAgICAgICAgICAgLy9cbiAgICAgICAgICAgIC8vIC8vIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24gKGV2ZW50KSB7XG4gICAgICAgICAgICAvLyAvL1xuICAgICAgICAgICAgLy8gLy8gICAgIC8vIElmIHRoZSBjbGlja2VkIGVsZW1lbnQgZG9lc24ndCBoYXZlIHRoZSByaWdodCBzZWxlY3RvciwgYmFpbFxuICAgICAgICAgICAgLy8gLy8gICAgIGlmICghZXZlbnQudGFyZ2V0Lm1hdGNoZXMoJy5jbGljay1tZScpKSByZXR1cm47XG4gICAgICAgICAgICAvLyAvL1xuICAgICAgICAgICAgLy8gLy8gICAgIC8vIERvbid0IGZvbGxvdyB0aGUgbGlua1xuICAgICAgICAgICAgLy8gLy8gICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICAvLyAvL1xuICAgICAgICAgICAgLy8gLy8gICAgIC8vIExvZyB0aGUgY2xpY2tlZCBlbGVtZW50IGluIHRoZSBjb25zb2xlXG4gICAgICAgICAgICAvLyAvLyAgICAgY29uc29sZS5sb2coZXZlbnQudGFyZ2V0KTtcbiAgICAgICAgICAgIC8vIC8vXG4gICAgICAgICAgICAvLyAvLyB9LCBmYWxzZSk7XG4gICAgICAgIH1cbiAgICB9XG59XG4iXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBOzs7Ozs7Ozs7Ozs7O0FBZ0JBO0FBQ0E7QUFDQTtBQUVBO0FBRUE7QUFDQTtBQUFBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7OztBQXhFQTtBQUNBO0FBREE7QUFJQTs7O0FBR0E7QUFDQTtBQUVBOzs7QUFHQTtBQUNBO0FBWkE7QUFDQTsiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///GR9V\n");

/***/ }),

/***/ "HdrD":
/*!*************************************************************************************************************!*\
  !*** /Users/kiener/Webroot/sw6_repo/custom/plugins/KienerMyParcel/src/Resources/app/storefront/src/main.js ***!
  \*************************************************************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var regenerator_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! regenerator-runtime */ \"wcNg\");\n/* harmony import */ var regenerator_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(regenerator_runtime__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _myparcel_plugins_shipping_options_plugin__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./myparcel/plugins/shipping-options.plugin */ \"GR9V\");\n // Import all necessary Storefront plugins and scss files\n\n // Register them via the existing PluginManager\n\nvar PluginManager = window.PluginManager;\nPluginManager.register('MyParcelShippingOptions', _myparcel_plugins_shipping_options_plugin__WEBPACK_IMPORTED_MODULE_1__[\"default\"]); // Necessary for the webpack hot module reloading server\n\nif (false) {}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiSGRyRC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8vVXNlcnMva2llbmVyL1dlYnJvb3Qvc3c2X3JlcG8vY3VzdG9tL3BsdWdpbnMvS2llbmVyTXlQYXJjZWwvc3JjL1Jlc291cmNlcy9hcHAvc3RvcmVmcm9udC9zcmMvbWFpbi5qcz85N2JmIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCAncmVnZW5lcmF0b3ItcnVudGltZSc7XG5cbi8vIEltcG9ydCBhbGwgbmVjZXNzYXJ5IFN0b3JlZnJvbnQgcGx1Z2lucyBhbmQgc2NzcyBmaWxlc1xuaW1wb3J0IE15UGFyY2VsU2hpcHBpbmdPcHRpb25zXG4gICAgZnJvbSAnLi9teXBhcmNlbC9wbHVnaW5zL3NoaXBwaW5nLW9wdGlvbnMucGx1Z2luJztcblxuLy8gUmVnaXN0ZXIgdGhlbSB2aWEgdGhlIGV4aXN0aW5nIFBsdWdpbk1hbmFnZXJcbmNvbnN0IFBsdWdpbk1hbmFnZXIgPSB3aW5kb3cuUGx1Z2luTWFuYWdlcjtcblBsdWdpbk1hbmFnZXIucmVnaXN0ZXIoJ015UGFyY2VsU2hpcHBpbmdPcHRpb25zJywgTXlQYXJjZWxTaGlwcGluZ09wdGlvbnMpO1xuXG4vLyBOZWNlc3NhcnkgZm9yIHRoZSB3ZWJwYWNrIGhvdCBtb2R1bGUgcmVsb2FkaW5nIHNlcnZlclxuaWYgKG1vZHVsZS5ob3QpIHtcbiAgICBtb2R1bGUuaG90LmFjY2VwdCgpO1xufVxuIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFFQTtBQUNBO0FBR0E7QUFDQTtBQUNBO0FBRUEiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///HdrD\n");

/***/ })

},[["HdrD","runtime","vendor-node","vendor-shared"]]]);
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Ui/js/modal/confirm'
], function(
    $,
    quote,
    checkoutdata,
    confirmation
){
        var lpcGoogleMap,
            lpcOpenedInfoWindow,
            lpcMarkersArray = [],
            lpcModePR,

            lpcRelayId,
            lpcRelayType,
            lpcRelayName,
            lpcRelayAddress,
            lpcRelayCity,
            lpcRelayZipcode,
            lpcRelayCountry,
            lpcAjaxSetRelayInformationUrl,
            lpcWidgetRelayCountries;

        // Entry point to display map and markers (WS)
        var lpcShowRelaysMap = function(){

            lpcClearMarkers();

            if($(".lpc_layer_relay").length !== 0){
                var bounds = new google.maps.LatLngBounds();

                $(".lpc_layer_relay").each(function(index, element){
                    var relayPosition = new google.maps.LatLng($(this).find('.lpc_layer_relay_latitude').text(), $(this).find(
                        '.lpc_layer_relay_longitude').text());

                    var markerLpc = new google.maps.Marker({
                        map: lpcGoogleMap,
                        position: relayPosition,
                        title: $(this).find('.lpc_layer_relay_name').text(),
                        icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    });

                    var infowindowLpc = lpcInfoWindowGenerator($(this));
                    lpcAttachClickInfoWindow(markerLpc, infowindowLpc, index);
                    lpcAttachClickChooseRelay(element);

                    lpcMarkersArray.push(markerLpc);
                    bounds.extend(relayPosition);
                });
                lpcGoogleMap.fitBounds(bounds);
            }
        };

        // Clean old markers (WS)
        var lpcClearMarkers = function(){
            lpcMarkersArray.forEach(function(element){
                element.setMap(null);
            });

            lpcMarkersArray.length = 0;
        };

        // Create marker popup content (WS)
        var lpcInfoWindowGenerator = function(relay){
            var indexRelay = relay.find(".lpc_relay_choose").attr('data-relayindex');

            var contentString = '<div class="info_window_lpc">';
            contentString += '<span class="lpc_store_name">' + relay.find('.lpc_layer_relay_name').text() + '</span>';
            contentString += '<span class="lpc_store_address">' + relay.find('.lpc_layer_relay_address_street').text() + '<br>' + relay.find(
                    '.lpc_layer_relay_address_zipcode').text() + ' ' + relay.find('.lpc_layer_relay_address_city').text() + '</span>';
            contentString += '<span class="lpc_store_schedule">' + relay.find('.lpc_layer_relay_schedule').html() + '</span>';
            contentString += '<div class="lpc_relay_choose lpc_relay_popup_choose" data-relayindex=' + indexRelay + '>' + $.mage.__(
                    "Choose this relay") + '</div>';
            contentString += '</div>';

            var infowindow = new google.maps.InfoWindow({
                content: contentString
            });

            return infowindow;
        };

        // Add display relay detail click event (WS)
        var lpcAttachClickInfoWindow = function(marker, infoWindow, index){

            marker.addListener('click', function(){
                lpcClickHandler(marker, infoWindow);
            });

            $("#lpc_layer_relay_" + index).click(function(){
                lpcClickHandler(marker, infoWindow);
            });
        };

        // Display details on markers (WS)
        var lpcClickHandler = function(marker, infoWindow){

            if(lpcOpenedInfoWindow){
                lpcOpenedInfoWindow.close();
            }

            infoWindow.open(lpcGoogleMap, marker);
            lpcOpenedInfoWindow = infoWindow;
        };

        // Add action on click choose relay (WS)
        var lpcAttachClickChooseRelay = function(element){
            var divChooseRelay = $(element).find(".lpc_relay_choose");
            var relayIndex = divChooseRelay.attr("data-relayindex");

            $(document).on('click', '.lpc_relay_choose[data-relayindex=' + relayIndex + ']', function(){
                lpcAttachOnclickConfirmationRelay(relayIndex);
            })
        };

        var lpcMapResize = function(){
            google.maps.event.trigger(lpcGoogleMap, "resize");
        };

        // Confirm relay choice (WS)
        var lpcAttachOnclickConfirmationRelay = function(relayIndex){
            var relayClicked = $("#lpc_layer_relay_" + relayIndex);

            if(relayClicked !== null){
                var lpcRelayIdTmp = relayClicked.find('.lpc_layer_relay_id').text();
                var lpcRelayNameTmp = relayClicked.find('.lpc_layer_relay_name').text();
                var lpcRelayAddressTmp = relayClicked.find('.lpc_layer_relay_address_street').text();
                var lpcRelayCityTmp = relayClicked.find('.lpc_layer_relay_address_city').text();
                var lpcRelayZipcodeTmp = relayClicked.find('.lpc_layer_relay_address_zipcode').text();
                var lpcRelayTypeTmp = relayClicked.find('.lpc_layer_relay_type').text();
                var lpcRelayCountryTmp = relayClicked.find('.lpc_layer_relay_country_code').text();

                confirmation({
                    title: $.mage.__('Confirm relay'),
                    content: $.mage.__('Do you confirm the shipment to this relay:') + '<br>' + lpcRelayNameTmp + '<br>' + lpcRelayAddressTmp + '<br>' + lpcRelayZipcodeTmp + ' ' + lpcRelayCityTmp,
                    actions: {
                        confirm: function(){
                            lpcChooseRelay(lpcRelayIdTmp, lpcRelayNameTmp, lpcRelayAddressTmp, lpcRelayZipcodeTmp, lpcRelayCityTmp, lpcRelayTypeTmp, lpcRelayCountryTmp);
                        }, cancel: function(){
                        }, always: function(){
                        }
                    }
                });
            }
        };

        // Apply choosen relay after user confirmation
        var lpcChooseRelay = function(lpcRelayId, lpcRelayName, lpcRelayAddress, lpcRelayZipcode, lpcRelayCity, lpcRelayType, lpcRelayCountry){
            lpcSetRelayData(lpcRelayId, lpcRelayName, lpcRelayAddress, lpcRelayCity, lpcRelayZipcode, lpcRelayType, lpcRelayCountry);
            lpcSetSessionRelayInformation(lpcRelayId, lpcRelayName, lpcRelayAddress, lpcRelayZipcode, lpcRelayCity, lpcRelayType, lpcRelayCountry);
            lpcAppendChosenRelay(lpcRelayName, lpcRelayAddress, lpcRelayZipcode, lpcRelayCity);
            $('#' + lpcModePR).modal('closeModal');
        };

        // Add relay informaiton in session to use them when validating order
        var lpcSetSessionRelayInformation = function(relayId, relayName, relayAddress, relayZipCode, relayCity, lpcRelayType, relayCountry){
            if(relayId.length != 0){
                $.ajax({
                    url: lpcAjaxSetRelayInformationUrl, type: 'POST', dataType: 'json', data: {
                        relayId: relayId, relayName: relayName, relayAddress: relayAddress, relayPostCode: relayZipCode, relayCity: relayCity, relayCountry: relayCountry, relayType: lpcRelayType
                    }, complete: function(response){
                    }
                });
            }
        };

        // Add relay information under the shipping method choice
        var lpcAppendChosenRelay = function(nameRelay, addressRelay, zipcodeRelay, cityRelay){
            var chosenRelay = '<p>' + nameRelay + '</p><p>' + addressRelay + '</p><p>' + zipcodeRelay + ' ' + cityRelay + '</p>' + '<p id="lpc_change_my_relay">' + $.mage.__(
                    'Modify my relay') + '</p>';


            if($('#lpc_chosen_relay').length){
                $('#lpc_chosen_relay').html(chosenRelay);
            }else{
                $("<div>").attr('id', 'lpc_chosen_relay').appendTo('#label_method_pr_colissimo');
                $('#lpc_chosen_relay').html(chosenRelay);
            }
        };

        // Set relay values
        var lpcSetRelayData = function(lpcRelayIdTmp, lpcRelayNameTmp, lpcRelayAddressTmp, lpcRelayCityTmp, lpcRelayZipcodeTmp, lpcRelayTypeTmp, lpcRelayCountryTmp){
            lpcRelayId = lpcRelayIdTmp;
            lpcRelayName = lpcRelayNameTmp;
            lpcRelayAddress = lpcRelayAddressTmp;
            lpcRelayCity = lpcRelayCityTmp;
            lpcRelayZipcode = lpcRelayZipcodeTmp;
            lpcRelayType = lpcRelayTypeTmp;
            lpcRelayCountry = lpcRelayCountryTmp;
        };

        return {
            lpcLoadMap: function(){
                var mapOptions = {
                    zoom: 10, mapTypeId: google.maps.MapTypeId.ROADMAP, center: {lat: 48.866667, lng: 2.333333}, disableDefaultUI: true
                };
                lpcGoogleMap = new google.maps.Map(document.getElementById("lpc_map"), mapOptions);
            },

            // create modal to display relay choice
            lpcAttachOnClickPopup: function(shippingMethod, modal, quote, addressList, checkoutData){
                var carrierCode = shippingMethod['carrier_code'];
                var methodCode = shippingMethod['method_code'];

                var shippingAddress;

                shippingAddress = quote.shippingAddress();

                if(carrierCode == "colissimo" && methodCode == "pr"){
                    if($("#lpc_layer_relays").length){
                        lpcModePR = "lpc_layer_relays";

                        var modalOptions = {
                            buttons: [], responsive: true, innerScroll: true,
                        };

                        var $divPopupLpc = $('#lpc_layer_relays');
                        var popup = modal(modalOptions, $divPopupLpc);

                        popup.openModal();

                        $("#lpc_modal_relays_search_address").val(function(){
                            return (shippingAddress.street == undefined || shippingAddress.street['0'].length == 0
                                ? ""
                                : shippingAddress.street['0']);
                        });

                        $("#lpc_modal_relays_search_zipcode").val(function(){
                            return (shippingAddress.postcode == undefined || shippingAddress.postcode.length == 0
                                ? ""
                                : shippingAddress.postcode);
                        });

                        $("#lpc_modal_relays_search_city").val(function(){
                            return (shippingAddress.city == undefined || shippingAddress.city.length == 0
                                ? ""
                                : shippingAddress.city);
                        });

                        $("#lpc_layer_button_search").click();

                        $("#lpc_chosen_relay").html("");

                        if(typeof google !== "undefined"){
                            lpcMapResize();
                        }else{
                            console.error(
                                'Google is not defined. Please check if an API key is set in the configuration (Stores->Configuration->Sales->La Poste Colissimo Advanced Setup)');
                        }
                    }else if($("#lpc_layer_widget").length){
                        lpcModePR = "lpc_layer_widget";

                        var modalOptions = {
                            buttons: [],
                            responsive: true,
                            wrapperClass: 'modals-wrapper lpc_modals-wrapper',
                            modalVisibleClass: '_show _show_lpc_relay'
                        };

                        var $divPopupLpc = $('#lpc_layer_widget');
                        var popup = modal(modalOptions, $divPopupLpc);

                        popup.openModal();

                        $("#lpc_widget_container").frameColissimoOpen({
                            "ceLang": "fr",
                            "ceCountryList": lpcWidgetRelayCountries,
                            "ceCountry": shippingAddress.countryId.length == 0
                                ? ""
                                : shippingAddress.countryId,
                            "dyPreparationTime": $('#lpc_average_preparation_delay').val() == ""
                                ? "1"
                                : $('#lpc_average_preparation_delay').val(),
                            "ceAddress": shippingAddress.street.length == 0
                                ? ""
                                : shippingAddress.street['0'],
                            "ceZipCode": shippingAddress.postcode.length == 0
                                ? ""
                                : shippingAddress.postcode,
                            "ceTown": shippingAddress.city.length == 0
                                ? ""
                                : shippingAddress.city,
                            "token": $("#lpc_token_widget").val(),
                            "URLColissimo": "https://ws.colissimo.fr",
                            "callBackFrame": "lpcCallBackFrame"
                        });
                    }else if($("#lpc_error_pr").length){
                        lpcModePR = "lpc_error_pr";

                        var modalOptions = {
                            buttons: [], responsive: true
                        };

                        var $divPopupLpcError = $('#lpc_error_pr');
                        var popup = modal(modalOptions, $divPopupLpcError);

                        popup.openModal();
                    }
                }
            },

            // Load relays for an address
            lpcLoadRelaysList: function(ajaxUrlLoadRelay){
                var $address = $("#lpc_modal_relays_search_address").val();
                var $zipcode = $("#lpc_modal_relays_search_zipcode").val();
                var $city = $("#lpc_modal_relays_search_city").val();

                var $errorDiv = $('#lpc_layer_error_message');
                var $listRelaysDiv = $('#lpc_layer_list_relays');

                var $loader = $('#lpc_layer_relays_loader');

                var countryId = quote.shippingAddress().countryId;

                $.ajax({
                    url: ajaxUrlLoadRelay, type: 'POST', dataType: 'json', data: {
                        address: $address, zipCode: $zipcode, city: $city, countryId: countryId,
                    }, beforeSend: function(){
                        $errorDiv.hide();
                        $listRelaysDiv.hide();
                        $loader.show();
                    }, success: function(response){
                        $loader.hide();
                        if(response.success == "1"){
                            $listRelaysDiv.html(response.html);
                            $listRelaysDiv.show();
                            lpcShowRelaysMap();
                            lpcMapResize();
                        }else{
                            $errorDiv.html(response.error);
                            $errorDiv.show();
                        }
                    }
                });
            },

            lpcSetAjaxSetRelayInformationUrl: function(AjaxSetRelayInformationUrl){
                lpcAjaxSetRelayInformationUrl = AjaxSetRelayInformationUrl;
            },

            lpcPublicSetRelayId: function(relayId){
                lpcRelayId = relayId;
            },

            lpcGetRelayId: function(){
                return lpcRelayId;
            },

            lpcGetRelayName: function(){
                return lpcRelayName;
            },

            lpcGetRelayCity: function(){
                return lpcRelayCity;
            },

            lpcGetRelayAddress: function(){
                return lpcRelayAddress;
            },

            lpcGetRelayZipcode: function(){
                return lpcRelayZipcode;
            },

            lpcGetRelayCountry: function(){
                return lpcRelayCountry;
            },

            // Apply relay chosen with widget method
            lpcCallBackFrame: function(point){
                var lpcRelayIdTmp = point['identifiant'];
                var lpcRelayNameTmp = point['nom'];
                var lpcRelayAddressTmp = point['adresse1'];
                var lpcRelayZipcodeTmp = point['codePostal'];
                var lpcRelayCityTmp = point['localite'];
                var lpcRelayTypeTmp = point['typeDePoint'];
                var lpcRelayCountryTmp = point['codePays'];

                lpcChooseRelay(lpcRelayIdTmp, lpcRelayNameTmp, lpcRelayAddressTmp, lpcRelayZipcodeTmp, lpcRelayCityTmp, lpcRelayTypeTmp, lpcRelayCountryTmp);
            },

            lpcSetWidgetRelayCountries: function(relayCountries){
                lpcWidgetRelayCountries = relayCountries;
            },
        }
    });

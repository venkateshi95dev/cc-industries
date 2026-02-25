/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */



const server = true;

class EBiz3DSecure {
    constructor(SecurityToken) {
        this.intervalPointer = 0;
        var urlParams = new URLSearchParams(window.location.search);
        var hid3DSecurityToken = urlParams.get('hid3DSecurityToken');
        var hidMerchantSecurityToken = urlParams.get('hidMerchantSecurityToken');

        if (hid3DSecurityToken != null || hid3DSecurityToken == "")
            this.guid = hid3DSecurityToken;
        else {
            this.server = true;
            var obj = document.getElementById("hid3DSecurityToken");
            if (obj != null) {
                this.guid = obj.value;
            } else
                this.server = false;
        }

        if (hidMerchantSecurityToken != null || hidMerchantSecurityToken == "")
            this.merchantSecurityToken = hidMerchantSecurityToken;
        else {
            this.server = true;
            var obj = document.getElementById("hidMerchantSecurityToken");
            if (obj != null) {
                this.merchantSecurityToken = obj.value;
            } else {
                this.merchantSecurityToken = SecurityToken;
                this.server = false;
            }
        }


        // this.hostUrl = "https://localhost:7257/";
       // this.hostUrl = "https://ebizcharge3ds.azurewebsites.net/";
      //  this.hostUrl = "https://ebizchargeapps101-3dsecure.azurewebsites.net/";
        //this.hostUrl = "https://3dsapi.ebizcharge.net/";
        this.hostUrl = "https://ebizcharge3ds-staging1.azurewebsites.net/"
        this.JWT = "";
        this.AuthCancel = false;
        this.AuthenticateCallback = null;
        //${this.hostUrl}

        fetch(`${this.hostUrl}V2/CreateJWT`,
            {
                method: 'get',
                headers: {
                    'Content-Type': 'application/json',
                    'securitytoken': this.merchantSecurityToken,
                    'relay_me': '1',
                },
            }).then((data) => {
            data.json().then((objData) => {
                this.JWT = objData.jwt;
                this.createSongBird().then((data) => {
                }).catch((data) => {
                    alert("Cannot create JWT");

                });
            });
        }).catch((data) => {
            console.log(data);
        });

    }

    start = () => {
        if (this.intervalPointer == 0)
            this.intervalPointer = setInterval(this.getConnectionstatus, 3000);
    }

    stop = () => {
        clearInterval(this.intervalPointer);
        this.intervalPointer = 0;
    }

    getConnectionstatus = () => {
        if (this.guid == null || this.guid == "undefined") {
            if (hid3DSecurityToken != null || hid3DSecurityToken == "")
                this.guid = hid3DSecurityToken;
            else {
                this.server = true;
                var obj = document.getElementById("hid3DSecurityToken");
                if (obj != null) {
                    this.guid = obj.value;
                } else
                    this.server = false;
            }
            if (this.guid == null || this.guid == "undefined") {
                return;
            }
        }
        fetch(`${this.hostUrl}V2/GetConnectionStatus/${this.guid}`,
            {
                method: 'get',
                headers: {
                    'Content-Type': 'application/json',
                    'GUID': this.guid,
                    'SessionId': this.sessionId,
                },
            }).then((data) => {
            data.json().then((objData) => {

                if (objData.connectionStatus == "Challenge") {
                    clearInterval(this.intervalPointer);
                    this.intervalPointer = 0;
                    this.TransactionId = objData.transactionId;
                    this.cardinalContinue(objData.acsurl, objData.transactionId, objData.payload, this.Amount, this.CurrencyCode);
                } else if (objData.ConnectionStatus == "NoChallenge") {
                    clearInterval(this.intervalPointer);
                    this.intervalPointer = 0;
                }

            });
        }).catch((data) => {
            console.log(data);
        });
    }

    update3DsecureLogStatus = (guid, status, xid, cavv, paResStatus, eciFlag, authenticationType) => {
        if (this.server) {

            fetch(`${this.hostUrl}V2/Update3DSecureLogStatus/${guid}/${status}/${xid}/${cavv}/${paResStatus}/${eciFlag}/${authenticationType}`,
                {
                    method: 'get',
                    headers: {
                        'Content-Type': 'application/json',
                        'GUID': this.guid,
                    },
                }).then((data) => {
                data.json().then((objData) => {


                }).catch((data) => {
                });
            }).catch((data) => {
            });
        }

    }

    createSongBird = () => {
        var ret = new Promise((theSuccess, thefailure) => {
            try {
                var script = document.createElement('script');
                // script.src = "https://songbirdstag.cardinalcommerce.com/edge/v1/songbird.js";
                script.src = "https://songbirdstag.cardinalcommerce.com/cardinalcruise/v1/songbird.js";
                //script.src = "https://songbird.cardinalcommerce.com/edge/v1/songbird.js";
                document.getElementsByTagName('head')[0].appendChild(script);
                script.onload = () => {
                    Cardinal.configure({
                        timeout: 10000,
                        extendedTimeout: 4000,
                        maxRequestRetries: 2,
                        logging: {
                            level: "on"
                        },
                        payment: {
                            view: 'modal',
                            framework: 'cardinal',
                            displayLoading: true,
                            displayExitButton: false
                        },
                        visaCheckout: {
                            button: {

                                color: 'neutral',
                                size: '',
                                height: '94',
                                width: '425',
                                locale: ''
                            }
                        }

                    });

                    Cardinal.setup("init", {
                        jwt: this.JWT
                    });

                    Cardinal.on('payments.setupComplete', (setupCompleteData) => {
                        this.sessionId = setupCompleteData.sessionId;
                        if (this.sessionId == null || this.guid == null || this.sessionId == "undefined" || this.guid == "undefined") {
                            return;
                        }
                        fetch(`${this.hostUrl}V2/SetSessionId/${this.sessionId}/${this.guid}`,
                            {
                                method: 'get',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'GUID': this.guid,
                                    'SessionId': this.sessionId,
                                },
                            }).then((data) => {

                        }).catch((data) => {
                        });
                    });


                    Cardinal.on("payments.validated", (data, jwt) => {
                        if (data.Payment.ExtendedData.ChallengeCancel == '01')
                            this.AuthCancel = true;
                        else
                            this.AuthCancel = false;
                        switch (data.ActionCode) {
                            case "SUCCESS":
                                this.Authenticate("S");
                                break;

                            case "NOACTION":
                                this.Authenticate("N");
                                break;

                            case "FAILURE":
                                this.Authenticate("F");
                                break;

                            case "ERROR":
                                this.Authenticate("E");
                                break;
                        }
                    });

                    theSuccess("Script loaded and ready!");
                };


            } catch (ex) {
                thefailure(ex.errorMessage);
            }
        });

        return ret;
    }


    cardinalContinue = (AcsUrl, TransactionId, Payload, OrderNumber, Amount, CurrencyCode) => {
        this.TransactionId = TransactionId;
        Cardinal.continue('cca',
            {
                "AcsUrl": AcsUrl,
                "Payload": Payload
            },
            {
                "OrderDetails": {
                    "TransactionId": TransactionId,
                    "OrderNumber": OrderNumber,
                    "Amount": Amount,
                    "CurrencyCode": CurrencyCode
                }
            });
    }

    cardinalTrigger = (bintxt) => {
        var bin = document.getElementById(bintxt);
        Cardinal.trigger("bin.process", bin.value)
            .then(function (results) {
                if (results.Status) {
                } else {
                    alert("Bin profiling failed");
                }

            })
            .catch(function (error) {
                alert("An error occurred during profiling: " + error);
            });
    }

    Authenticate = (ActionCode) => {
        var a = JSON.stringify({
            "TransactionId": this.TransactionId,
            "TransactionType": "C",
            "PAResPayload": "",
            "MerchantReferenceNumber": ""
        });
        fetch(`${this.hostUrl}V2/CMPI_Authenticate`, {
            method: 'post',
            headers: {
                //'Access-Control-Allow-Origin': true,
                'Content-Type': 'application/json',
                'securitytoken': this.merchantSecurityToken,
            },
            body: a
        }).then((data) => {
            data.json().then((objData) => {
                if (objData.cavv == '') {
                    objData.cavv = null;
                }
                if (objData.paResStatus == "Y" && objData.signatureVerification == "Y")
                    this.update3DsecureLogStatus(this.guid, "S", objData.xid, objData.cavv, objData.paResStatus, objData.eciFlag, objData.authenticationType);
                else
                    this.update3DsecureLogStatus(this.guid, ActionCode, objData.xid, objData.cavv, objData.paResStatus, objData.eciFlag, objData.authenticationType);
                if (this.AuthenticateCallback != null)
                    this.AuthenticateCallback(objData);
                clearInterval(this.intervalPointer);
            });
        }).catch((data) => {
            clearInterval(this.intervalPointer);
            this.intervalPointer = 0;
            if (this.AuthenticateCallback != null)
                this.AuthenticateCallback(objData);

        });

    }
    Lookup = (LookupData) => {
        var ret = new Promise((Success, Failure) => {

            try {
                this.AuthenticateCallback = (objdata) => {
                    //|| objdata.enrolled == "B"
                    if (objdata.errorNo == 0) {
                        if (((objdata.paResStatus == "Y" || objdata.paResStatus == "A") && objdata.signatureVerification == "Y")
                            || (objdata.enrolled == "U" && objdata.reasonCode != "101" && objdata.reasonCode != "402")
                            || (objdata.paResStatus != "C" && objdata.warning != undefined)) {
                            Success(objdata);
                        } else {
                            Failure(objdata);
                        }
                    } else
                        Failure(objdata);
                }

                var tempObj = {
                    TransactionMode: "S",
                    TransactionType: "C",

                }

                var t = JSON.stringify({
                    "CustomerIntermalId": LookupData.CustomerInternalId,
                    "MethodId": LookupData.MethodId,
                    "MerchantId": "",
                    "CardNumber": LookupData.CardNumber,
                    "DFReferenceId": this.sessionId,
                    "OrderNumber": LookupData.OrderNumber,
                    "Amount": LookupData.Amount,
                    "CurrencyCode": LookupData.CurrencyCode,
                    "CardExpMonth": LookupData.ExpireMonth,
                    "CardExpYear": LookupData.ExpireYear,
                    "AcquirerId": "",
                    "AcquirerMerchantId": "",
                    "BillingAddress1": LookupData.BillingAddress,
                    "BillingCity": LookupData.BillingCity,
                    "BillingCountryCode": LookupData.BillingCountryCode,
                    "BillingFirstName": LookupData.BillingFirstName,
                    "BillingLastName": LookupData.BillingLastName,
                    "BillingPhone": LookupData.BillingPhone,
                    "BillingPostalCode": LookupData.Zip,
                    "BillingState": LookupData.BillingState,
                    "ShippingAddress1": LookupData.BillingAddress,
                    "ShippingCity": LookupData.BillingCity,
                    "ShippingCountryCode": LookupData.ShippingCountryCode,
                    "ShippingFirstName": LookupData.BillingFirstName,
                    "ShippingLastName": LookupData.BillingLastName,
                    "ShippingMethodIndicator": "",
                    "ShippingPostalCode": LookupData.Zip,
                    "ShippingState": LookupData.BillingState,
                    "ShippingAmount": LookupData.Amount,
                    "MobilePhone": LookupData.phone,
                    "Email": LookupData.Email,
                    "BrowserJavascriptEnabled": true,
                    "BrowserJavaEnabled": true,
                    "BrowserScreenHeight": window.innerHeight.toString(),
                    "BrowserScreenWidth": window.innerWidth.toString(),
                    "UserAgent": window.navigator.userAgent,
                    //  "UserAgent": "",
                    "BrowserHeader": "text/html",
                    "BrowserLanguage": "en_US",
                    "BrowserColorDepth": screen.colorDepth.toString(),
                    "IPAddress": "",
                    "DeviceChannel": "Browser",
                    "BrowserTimeZone": "",
                    "TransactionType": LookupData.TransactionType,
                    "TransactionMode": LookupData.TransactionMode,
                    "AcquirerCountryCode": "",
                    "BillingAddress2": "",
                    "BillingAddress3": "",
                    // "ShippingMethodIndicator": "",
                    "ShippingAddress2": "",
                    "ShippingAddress3": "",
                    "WorkPhone": "",
                    "AuthenticationIndicator": "01",
                    "ProductCode": "PHY",
                    "ThreeDSVersion": "",
                    "CardType": LookupData.CardType, //VSA - Visa, MSC - Mastercard, AMX - American Express, DSC - Discover
                    "DeliveryTimeframe": "",
                    "DeliveryEmail": "",
                    "ReorderIndicator": "",
                    "PreOrderIndicator": "",
                    "PreOrderDate": "",
                    "GiftCardAmount": "",
                    "GiftCardCurrencyCode": "",
                    "GiftCardCount": "",
                    "CardinalEncryptedData": "",
                    "AccountAgeIndicator": "",
                    "AccountCreateDate": "",
                    "AccountChangeIndicator": "",
                    "AccountChangeDate": "",
                    "AccountPwdChangeIndicator": "",
                    "AccountPwdChangeDate": "",
                    "ShippingAddressUsageIndicator": "",
                    "ShippingAddressUsageDate": "",
                    "TransactionCountDay": "",
                    "TransactionCountYear": "",
                    "AddCardAttempts": "",
                    "AccountPurchases": "",
                    "FraudActivity": "",
                    "ShippingNameIndicator": "",
                    "PaymentAccountIndicator": "",
                    "AlternateAuthenticationMethod": "",
                    "AlternateAuthenticationDate": "",
                    "AlternateAuthenticationData": "",
                    "AlternatePriorAuthenticationData": "",
                    "AlternatePriorAuthenticationMethod": "",
                    "AlternatePriorAuthenticationTime": "",
                    "AlternatePriorAuthenticationRef": "",
                    "Alias": "",
                    "Token": "",
                    "SdkMaxTimeout": "",
                    "SDKFlowType": "",
                    "ChallengeIndicator": "",
                    "ACSWindowSize": "",
                    "MerchantName": "",
                    //  "AcquirerId": "",
                    //  "AcquirerMerchantId": "",
                    "AcquirerPassword": "",
                    "RequestorId": "",
                    "RequestorName": "",
                    "CategoryCode": "",
                    "CountryCodeOverride": "",
                    "MessageCategory": "",
                    "MerchantURL": "",
                    "BillingFullName": "",
                    "AddressMatch": "",
                    "AccountId": "",
                    "Custom_X": "",
                    "Item_Desc_X": "",
                    "Item_Name_X": "",
                    "Item_Price_X": "",
                    "Item_Quantity_X": "",
                    "Item_ShippingAddress1_X": "",
                    "Item_ShippingAddress2_X": "",
                    "Item_ShippingCity_X": "",
                    "Item_ShippingCountryCode_X": "",
                    "Item_ShippingDestination_X": "",
                    "Item_ShippingFirstName_X": "",
                    "Item_ShippingLastName_X": "",
                    "Item_ShippingMiddleName_X": "",
                    "Item_ShippingPhone_X": "",
                    "Item_ShippingPostalCode_X": "",
                    "Item_ShippingState_X": "",
                    "Item_SKU_X": "",
                    "MerchantReferenceNumber": "",
                    "OrderDescription": "",
                    "ShippingDestination_X": "",
                    "ShippingMiddleName": "",
                    "TaxAmount": "",
                    "OverridePaymentMethod": "",
                    "ShippingFullName": "",
                    "DecoupledMaxTime": "",
                    "DecoupledIndicator": ""
                });

                fetch(`${this.hostUrl}V2/CMPI_Lookup`, {
                    method: 'post',
                    headers: {
                        'Access-Control-Allow-Origin': true,
                        'Content-Type': 'application/json',
                        'securitytoken': this.merchantSecurityToken,
                    },
                    body: t
                }).then((data) => {

                    data.json().then((objData) => {
                        this.TransactionId = objData.transactionId;
                        if (objData.acsUrl != null && objData.acsUrl != "" && objData.enrolled == "Y" && objData.errorNo == "0") {
                            this.cardinalContinue(objData.acsUrl, objData.transactionId, objData.payload, LookupData.OrderNumber, LookupData.Amount, LookupData.CurrencyCode);
                        } else {
                            this.AuthenticateCallback(objData);
                        }
                    });
                }).catch((data) => {
                    console.log(data);
                });
            } catch {
                Failure("Failure");
            }
        });
        return ret;


    }

    EBiz3DSecureCheck = (LookupData) => {
        this.CurrencyCode = LookupData.CurrencyCode;
        this.Amount = LookupData.Amount;
        var ret = new Promise((theSuccess, thefailure) => {
            try {
                if (this.JWT == null || this.JWT == "")
                    return null;
                this.Lookup(LookupData).then((data) => {
                    data.resultCode = "A";
                    console.log("sucsess:");
                    theSuccess(data);
                }).catch((data) => {
                    data.resultCode = "E";
                    console.log("failure:");
                    data.authCancel = this.AuthCancel;
                    this.AuthCancel = false;
                    thefailure(data);
                });
            } catch (ex) {
                thefailure(ex.errorMessage);
            }
        });
        return ret;
    }

    GetErrorMessage = (OutputData) => {
        var Message;
        Message = "";
        if (OutputData.authCancel == true) {
            Message = "Authentication Canceled!";
            return Message;
        }
        const errors_dictionary = new Array(
            new Array("", "0", "N", "Y", "07", "", OutputData.cardHolderInfo, "Error#:2 Authentication failed by card Issuer, please try another card."),
            new Array("", "0", "N", "Y", "00", "", OutputData.cardHolderInfo, "Error#:2 Authentication failed by card Issuer, please try another card."),
            new Array("", "0", "U", "Y", "07", "", "", "Error#:4 Authentication is unavailable at the current time, please try again."),
            new Array("", "0", "U", "Y", "00", "", "", "Error#:4 Authentication is unavailable at the current time, please try again."),
            new Array("", "0", "R", "Y", "07", "", "", "Error#:5 Rejected authentication by the issuer, please try another card."),
            new Array("", "0", "R", "Y", "00", "", "", "Error#:5 Rejected authentication by the issuer, please try another card."),
            new Array("", "0", "", "U", "07", "101", "", "Error#:6 Authentication is unavailable due to a system error, please try again later."),
            new Array("", "0", "", "U", "00", "101", "", "Error#:6 Authentication is unavailable due to a system error, please try again later."),
            new Array("", "1001", "", "U", "07", "101", "", "Error#:7 An error occurred downstream while attempting authentication processing, please try again later."),
            new Array("", "1001", "", "U", "00", "101", "", "Error#:7 An error occurred downstream while attempting authentication processing, please try again later."),
            new Array("", "2860", "", "U", "07", "402", "", "Error#:8 Timeout encountered while attempting authentication processing, please try again later."),
            new Array("", "2860", "", "U", "00", "402", "", "Error#:8 Timeout encountered while attempting authentication processing, please try again later."),
            new Array("03", "0", "N", "", "07", "", "", "Error#:10 Your card can't be authenticated, please try another card."),
            new Array("03", "0", "N", "", "00", "", "", "Error#:10 Your card can't be authenticated, please try another card."),
            new Array("03", "0", "U", "", "07", "", "", "Error#:11 Authentication is unavailable, please try again."),
            new Array("03", "0", "U", "", "00", "", "", "Error#:11 Authentication is unavailable, please try again."),
            new Array("03", "1050", "U", "", "07", "", "", "Error#:12 A system error occurred while attempting to process the authentication request, please try another card."),
            new Array("03", "1050", "U", "", "00", "", "", "Error#:12 A system error occurred while attempting to process the authentication request, please try another card."),
            new Array("03", "1050", "N", "", "07", "", "", "Authentication Canceled!"),
            new Array("03", "1050", "N", "", "00", "", "", "Authentication Canceled!"),
            new Array("", "0", "", "B", "07", "", "", "Error#:13 Authentication is unavailable, please try another card."),
            new Array("", "0", "", "B", "00", "", "", "Error#:13 Authentication is unavailable, please try another card."),
            new Array("", "0", "C", "Y", "05", "", "", "Error#:14 Authentication is unavailable, please try another card."),
            new Array("", "0", "C", "Y", "00", "", "", "Error#:14 Authentication is unavailable, please try another card."),
        );

        if (OutputData.authenticationType == undefined || OutputData.authenticationType == null) OutputData.authenticationType = "";
        if (OutputData.errorNo == null) OutputData.errorNo = "";
        if (OutputData.paResStatus == null) OutputData.paResStatus = "";
        if (OutputData.enrolled == null) OutputData.enrolled = "";
        if (OutputData.eciFlag == null) OutputData.eciFlag = "";
        if (OutputData.reasonCode == null) OutputData.reasonCode = "";

        errors_dictionary.forEach(row => {

            if (row[0] == OutputData.authenticationType && row[1] == OutputData.errorNo &&
                row[2] == OutputData.paResStatus && row[3] == OutputData.enrolled &&
                row[4] == OutputData.eciFlag && row[5] == OutputData.reasonCode) {
                Message = row[7] + " " + row[6];

            }
        });
        return Message;
    }
}
let eBiz3DSecure;
let securityToken = {
    Password: document.getElementById('ebzc-p').dataset.token ?? "",
    UserId: document.getElementById('ebzc-u').dataset.token ?? "",
    SecurityId: document.getElementById('ebzc-k').dataset.token ?? ""
};
/**
 * 3DSecure connection
 */
window.eBiz3DSecure = new EBiz3DSecure(JSON.stringify(securityToken));


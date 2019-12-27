window.PluginManager = function () {
    this.plugin = null;
    this.certNumber = null;
    this.certs = [];
    this.data = undefined;

    var self = this;

    var getDeviceTypeNameMap = function (plugin) {
        var result = {};

        result[plugin.TOKEN_TYPE_UNKNOWN] = 'Неизвестное устройство';
        result[plugin.TOKEN_TYPE_KAZTOKEN] = 'KazToken';
        result[plugin.TOKEN_TYPE_RUTOKEN_ECP] = 'РуТокен ЭЦП';
        result[plugin.TOKEN_TYPE_RUTOKEN_WEB] = 'РуТокен Web';
        result[plugin.TOKEN_TYPE_RUTOKEN_PINPAD_IN] = 'РуТокен PINPad';
        result[plugin.TOKEN_TYPE_RUTOKEN_PINPAD_2] = 'РуТокен PINPad 2';

        return result;
    };

    var getErrorMessagesMap = function (plugin) {
        var result = {},
            codes = plugin.errorCodes;

        result[106] = 'Превышино максимально допустимое количество ввода неверного ПИН';

        result[codes.UNKNOWN_ERROR] = 'Неизвестная ошибка';
        result[codes.BAD_PARAMS] = 'Неправильные параметры';
        result[codes.DEVICE_NOT_FOUND] = 'Устройство не найдено';
        result[codes.CERTIFICATE_CATEGORY_BAD] = 'Недопустимый тип сертификата';
        result[codes.CERTIFICATE_EXISTS] = 'Сертификат уже существует на устройстве';
        result[codes.PKCS11_LOAD_FAILED] = 'Не удалось загрузить PKCS#11 библиотеку';
        result[codes.NOT_ENOUGH_MEMORY] = 'Недостаточно памяти';
        result[codes.PIN_LENGTH_INVALID] = 'Некорректная длина PIN-кода';
        result[codes.PIN_INCORRECT] = 'Некорректный PIN-код';
        result[codes.PIN_LOCKED] = 'PIN-код заблокирован';
        result[codes.PIN_CHANGED] = 'PIN-код был изменен';
        result[codes.SESSION_INVALID] = 'Состояние токена изменилось';
        result[codes.USER_NOT_LOGGED_IN] = 'Выполните вход на устройство';
        result[codes.KEY_NOT_FOUND] = 'Соответствующая сертификату ключевая пара не найдена';
        result[codes.KEY_ID_NOT_UNIQUE] = 'Идентификатор ключевой пары не уникален';
        result[codes.CERTIFICATE_NOT_FOUND] = 'Сертификат не найден';
        result[codes.CERTIFICATE_HASH_NOT_UNIQUE] = 'Хэш сертификата не уникален';
        result[codes.TOKEN_INVALID] = 'Ошибка чтения/записи устройства. Возможно, устройство было извлечено.';
        result[codes.BASE64_DECODE_FAILED] = 'Ошибка декодирования данных из BASE64';
        result[codes.PEM_ERROR] = 'Ошибка разбора PEM';
        result[codes.ASN1_ERROR] = 'Ошибка декодирования ASN1 структуры';
        result[codes.WRONG_KEY_TYPE] = 'Неправильный тип ключа';
        result[codes.NO_CA_CERTIFICATES_FOUND] = 'Сертификат УЦ не найден';
        result[codes.FILE_READ_ERROR] = 'Ошибка чтения файла';
        result[codes.FILE_WRITE_ERROR] = 'Ошибка записи файла';
        result[codes.FILE_ALREADY_EXISTS_ERROR] = 'Файл с таким именем уже существует';

        result[codes.X509_UNABLE_TO_GET_ISSUER_CERT] = 'Невозможно получить сертификат подписанта';
        result[codes.X509_UNABLE_TO_GET_CRL] = 'Невозможно получить CRL';
        result[codes.X509_UNABLE_TO_DECRYPT_CERT_SIGNATURE] = 'Невозможно расшифровать подпись сертификата';
        result[codes.X509_UNABLE_TO_DECRYPT_CRL_SIGNATURE] = 'Невозможно расшифровать подпись CRL';
        result[codes.X509_UNABLE_TO_DECODE_ISSUER_PUBLIC_KEY] = 'Невозможно раскодировать открытый ключ эмитента';
        result[codes.X509_CERT_SIGNATURE_FAILURE] = 'Неверная подпись сертификата';
        result[codes.X509_CRL_SIGNATURE_FAILURE] = 'Неверная подпись CRL';
        result[codes.X509_CERT_NOT_YET_VALID] = 'Срок действия сертификата еще не начался';
        result[codes.X509_CRL_NOT_YET_VALID] = 'Срок действия CRL еще не начался';
        result[codes.X509_CERT_HAS_EXPIRED] = 'Срок действия сертификата истек';
        result[codes.X509_CRL_HAS_EXPIRED] = 'Срок действия CRL истек';
        result[codes.X509_ERROR_IN_CERT_NOT_BEFORE_FIELD] = 'Некорректные данные в поле "notBefore" у сертификата';
        result[codes.X509_ERROR_IN_CERT_NOT_AFTER_FIELD] = 'Некорректные данные в поле "notAfter" у сертификата';
        result[codes.X509_ERROR_IN_CRL_LAST_UPDATE_FIELD] = 'Некорректные данные в поле "lastUpdate" у CRL';
        result[codes.X509_ERROR_IN_CRL_NEXT_UPDATE_FIELD] = 'Некорректные данные в поле "nextUpdate" у CRL';
        result[codes.X509_OUT_OF_MEM] = 'Не хватает памяти';
        result[codes.X509_DEPTH_ZERO_SELF_SIGNED_CERT] = 'Недоверенный самоподписанный сертификат';
        result[codes.X509_SELF_SIGNED_CERT_IN_CHAIN] = 'В цепочке обнаружен недоверенный самоподписанный сертификат';
        result[codes.X509_UNABLE_TO_GET_ISSUER_CERT_LOCALLY] = 'Невозможно получить локальный сертификат подписанта';
        result[codes.X509_UNABLE_TO_VERIFY_LEAF_SIGNATURE] = 'Невозможно проверить первый сертификат';
        result[codes.X509_CERT_CHAIN_TOO_LONG] = 'Слишком длинная цепочка сертификатов';
        result[codes.X509_CERT_REVOKED] = 'Сертификат отозван';
        result[codes.X509_INVALID_CA] = 'Неверный корневой сертификат';
        result[codes.X509_INVALID_NON_CA] = 'Неверный некорневой сертфикат, помеченный как корневой';
        result[codes.X509_PATH_LENGTH_EXCEEDED] = 'Превышена длина пути';
        result[codes.X509_PROXY_PATH_LENGTH_EXCEEDED] = 'Превышено количество промежуточных сертификатов';
        result[codes.X509_PROXY_CERTIFICATES_NOT_ALLOWED] = 'Промежуточные сертификаты недопустимы';
        result[codes.X509_INVALID_PURPOSE] = 'Неподдерживаемое назначение сертификата';
        result[codes.X509_CERT_UNTRUSTED] = 'Недоверенный сертификат';
        result[codes.X509_CERT_REJECTED] = 'Сертификат отклонен';
        result[codes.X509_APPLICATION_VERIFICATION] = 'Ошибка проверки приложения';
        result[codes.X509_SUBJECT_ISSUER_MISMATCH] = 'Несовпадения субьекта и эмитента';
        result[codes.X509_AKID_SKID_MISMATCH] = 'Несовпадение идентификатора ключа у субьекта и доверенного центра';
        result[codes.X509_AKID_ISSUER_SERIAL_MISMATCH] = 'Несовпадение серийного номера субьекта и доверенного центра';
        result[codes.X509_KEYUSAGE_NO_CERTSIGN] = 'Ключ не может быть использован для подписи сертификатов';
        result[codes.X509_UNABLE_TO_GET_CRL_ISSUER] = 'Невозможно получить CRL подписанта';
        result[codes.X509_UNHANDLED_CRITICAL_EXTENSION] = 'Неподдерживаемое расширение';
        result[codes.X509_KEYUSAGE_NO_CRL_SIGN] = 'Ключ не может быть использован для подписи CRL';
        result[codes.X509_KEYUSAGE_NO_DIGITAL_SIGNATURE] = 'Ключ не может быть использован для цифровой подписи';
        result[codes.X509_UNHANDLED_CRITICAL_CRL_EXTENSION] = 'Неподдерживаемое расширение CRL';
        result[codes.X509_INVALID_EXTENSION] = 'Неверное или некорректное расширение сертификата';
        result[codes.X509_INVALID_POLICY_EXTENSION] = 'Неверное или некорректное расширение политик сертификата';
        result[codes.X509_NO_EXPLICIT_POLICY] = 'Явные политики отсутствуют';
        result[codes.X509_DIFFERENT_CRL_SCOPE] = 'Другая область CRL';
        result[codes.X509_UNSUPPORTED_EXTENSION_FEATURE] = 'Неподдерживаемое расширение возможностей';
        result[codes.X509_UNNESTED_RESOURCE] = 'RFC 3779 неправильное наследование ресурсов';
        result[codes.X509_PERMITTED_VIOLATION] = 'Неправильная структура сертификата';
        result[codes.X509_EXCLUDED_VIOLATION] = 'Неправильная структура сертификата';
        result[codes.X509_SUBTREE_MINMAX] = 'Неправильная структура сертификата';
        result[codes.X509_UNSUPPORTED_CONSTRAINT_TYPE] = 'Неправильная структура сертификата';
        result[codes.X509_UNSUPPORTED_CONSTRAINT_SYNTAX] = 'Неправильная структура сертификата';
        result[codes.X509_UNSUPPORTED_NAME_SYNTAX] = 'Неправильная структура сертификата';
        result[codes.X509_CRL_PATH_VALIDATION_ERROR] = 'Неправильный путь CRL';

        return result;
    };

    this.getErrorMessage = function (code) {
        var plugin = this.plugin,
            errorMap = getErrorMessagesMap(plugin);

        return errorMap[code] || "Неизвестная ошибка";
    };

    this.resultCallback = function (result) {
        var plugin = this.plugin,
            deviceMap = getDeviceTypeNameMap(plugin);

        return deviceMap[result] || "Неизвестное устройство";
    };

    this.getTokenItems = function () {
        function createFn(id) {
            return function (res) {
                return {
                    id: id,
                    name: self.resultCallback(res)
                };
            };
        };

        return self.plugin.enumerateDevices()
            .then(function (devices) {
                var dLen = devices.length, k, promises = [];
                if (dLen)
                    for (k = 0; k < dLen; k++) {
                        var promise = self.plugin.getDeviceInfo(k, self.plugin.TOKEN_INFO_DEVICE_TYPE)
                            .then(createFn(k)
                                //, function (err) {
                                //console.log.apply(console, arguments);
                                //}
                            );
                        promises.push(promise);
                    }
                return Promise.all(promises);
            }, function (error) {
                console.log(error);
            });
    };

    this.onPluginLoadedPromises = function (pluginObject) {
        if (!pluginObject) return;
        return pluginObject.enumerateDevices()
            .then(function (devices) {
                return pluginObject.getDeviceInfo(devices[0], pluginObject.TOKEN_INFO_SERIAL);
            }).then(function (info) {
                return Promise.resolve(info);
            }, function (err) {
                console.log(err);
            });
    };

    this.getDeviceInfo = function () {
        /* try {
             await this.getPlugin();
 
             let devices = await self.plugin.enumerateDevices();
             let info = await self.plugin.getDeviceInfo(devices[0], self.plugin.TOKEN_INFO_SERIAL);
             return info;
 
         } catch (err) {
             console.log(err);
         }*/
        return this.getPlugin()
            .then(function () {
                return self.plugin.enumerateDevices();
            })
            .then(function (devices) {
                return self.plugin.getDeviceInfo(devices[0], self.plugin.TOKEN_INFO_SERIAL);
            })
            .then(function (info) {
                return Promise.resolve(info);
            }, function (err) {
                console.log(err);
            });
    };

    this.getPlugin = function () {
        if (self.plugin != null)
            return Promise.resolve(self.plugin);

        return rutoken.ready.then(function () {
            if (window.chrome && window.chrome.webstore) {
                return rutoken.isExtensionInstalled();
            } else {
                return Promise.resolve(true);
            }
        }).then(function (result) {
            if (result) {
                return rutoken.isPluginInstalled();
            } else {
                throw "Rutoken Extension wasn't found";
            }
        }).then(function (result) {
            if (result) {
                return rutoken.loadPlugin();
            } else {
                throw "Плагин не найден";
            }
        }).then(function (wrappedPlugin) {
            if (!wrappedPlugin.valid) {
                alert("Плагин не доступен в вашем браузере");
            }
            self.plugin = wrappedPlugin;
            return wrappedPlugin;
        }).then(undefined, function (reason) {
            console.log(reason);
        });
    };

    this.getTokenList = function () {
        return self.getPlugin()
            .then(function () {
                return self.getTokenItems();
            });
    };

    this.login = function (token, pin) {
        return this.getPlugin()
            .then(function () {
                return self.plugin.enumerateDevices();
            })
            .then(function (devices) {
                if (devices.length == 0) {
                    var error = { message: 'Устройство не найдено' };
                    console.log('Устройство не найдено');
                    return Promise.reject(error);
                }
                if (!pin) {
                    var error = { message: 'Не введен пин' };
                    console.log('Не введен пин');
                    return Promise.reject(error);
                }
                return self.plugin.login(token, pin);
            })
            .then(function () {
                return self.plugin.enumerateCertificates(token, 0);
            })
            .then(function (certNumber) {
                if (certNumber.length == 0) {
                    var error = { message: 'Cертификаты не найдены на устройстве' };
                    console.log('Cертификаты не найдены на устройстве');
                    return Promise.reject(error);
                }
                self.certNumber = certNumber[0];
                self.certs = certNumber;
                return self.plugin.authenticate(token, self.certNumber, "test");
            })
            .catch(function (error) {
                var resultError = {
                    message: ''
                };

                if (error.message && isNaN(error.message)) {//message defined and it is a text
                    resultError = error;
                } else {
                    if (!isNaN(error.message))//chrome
                        resultError.message = self.getErrorMessage(+error.message);
                    if (!isNaN(error))//opera, firefox
                        resultError.message = self.getErrorMessage(+error);
                }

                return Promise.reject(resultError);
            });
    };

    this.sign = function (device, data, pin) {
        this.data = data;
        return this.login(device, pin)
            .then(function () {
                var options = {
                    detached: true,
                    addUserCertificate: true,
                    addSignTime: true,
                    useHardwareHash: false
                };
                return self.plugin.sign(device, self.certNumber, self.data, false, options);
            });
    };

    this.doubleSign = function (device, data, pin) {
        this.data = data;
        return this.login(device, pin)
            .then(function () {
                var certsCount = self.certs.length, j, promises = [],
                    options = {
                        detached: true,
                        addUserCertificate: true,
                        addSignTime: true,
                        useHardwareHash: false
                    };
                for (j = 0; j < certsCount; j++) {
                    var promise = self.plugin.sign(device, self.certs[j], self.data, false, options);
                    promises.push(promise);
                }
                return Promise.all(promises);
            });
    };
};

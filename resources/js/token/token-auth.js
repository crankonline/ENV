$(window).load(function () {
    window.poh = {};

    var pluginManager = new PluginManager();

    var writeSerialNumber = function () {
        window.tokenNumber = "";
        return pluginManager.getDeviceInfo()
            .then(function (vals) {
                if (vals) {
                    window.tokenNumber = vals;
                }
            });
    };
    window.writeSerialNumber = writeSerialNumber;
});
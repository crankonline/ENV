$(window).load(function () {
    window.poh = {};

    var pluginManager = new PluginManager();

    var writeSerialNumber = function () {

        var createOpt = function (id, name) {
            window.tokenNumber = name;
        };

        pluginManager.getDeviceInfo()
            .then(function (vals) {
                if (vals) {
                    createOpt(0, vals);
                }

                watches = setTimeout(function () {
                    writeSerialNumber();
                }, 2000);
            });
    };

    writeSerialNumber();

});
